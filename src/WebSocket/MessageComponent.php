<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\WebSocket;


use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Events\EventDispatcherInterface;
use Swift\HttpFoundation\Exception\NotFoundException;
use Swift\Runtime\Cli\ConsoleLogger;
use Swift\Runtime\RuntimeDiTags;
use Swift\Serializer\Json;
use Swift\WebSocket\Router\SocketRouter;
use Swift\WebSocket\Security\Authentication\AuthenticationManager;

#[Autowire]
final class MessageComponent {
    
    protected \SplObjectStorage $clients;
    /** @var \Swift\WebSocket\Controller\WebSocketControllerInterface[] $controllers */
    protected array $controllers;
    
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly SocketRouter             $socketRouter,
        private readonly ConsoleLogger            $consoleLogger,
        private readonly AuthenticationManager    $authenticationManager,
    ) {
        $this->clients = new \SplObjectStorage();
    }
    
    /**
     * @inheritDoc
     */
    public function onOpen( \Swift\WebSocket\WsConnection $conn ): void {
        $this->clients->attach( $conn );
        
        $this->consoleLogger->getIo()->writeln( "New connection! ({$conn->getResourceId()})" );
        
        $controller = $this->getControllerForConnection( $conn );
        
        if ( ! $controller ) {
            return;
        }
        
        $response = $controller->onOpen( $conn );
        $response?->send( $conn );
    }
    
    /**
     * @inheritDoc
     */
    public function onClose( \Swift\WebSocket\WsConnection $conn ): void {
        if ( ! $controller = $this->getControllerForConnection( $conn ) ) {
            return;
        }
        
        $response = $controller->onClose( $conn );
        $response?->send( $conn );
        
        $this->clients->detach( $conn );
        
        $this->consoleLogger->getIo()->writeln( "Connection {$conn->getResourceId()} has disconnected" );
    }
    
    /**
     * @inheritDoc
     */
    public function onError( \Swift\WebSocket\WsConnection $conn, \Swift\WebSocket\Exceptions\WebsocketErrorException $e ): void {
        $this->consoleLogger->getIo()->writeln( "An error has occurred: {$e->getMessage()}" );
        
        $conn->close();
    }
    
    /**
     * @inheritDoc
     */
    public function onMessage( \Swift\WebSocket\WsConnection $from, $msg ): void {
        if ( empty( $from->getMessages() ) ) {
            $payload = ( new Json( $msg->getPayload() ) )->modeObject()->unSerialize();
            if ( $payload->type === 'auth' ) {
                $req           = $from->getRequest()->withAddedHeader( 'Authorization', $payload->payload->token );
                $from->request = $req;
                $from->passport = $this->authenticationManager->authenticate( $req, $from, function () use ( $from ) {
                    $from->close();
                    $this->onClose( $from );
                } );
                $from->addMessage( $msg );
                
                return;
            }
        }
        
        $from->addMessage( $msg );
        $numRecv = count( $this->clients ) - 1;
        $this->consoleLogger->getIo()->writeln(
            sprintf(
                  '<info>Connection %d sending message "%s" to %d other connection%s' . "\n</info>"
                , $from->getResourceId(), $msg, $numRecv, $numRecv === 1 ? '' : 's'
            )
        );
        
        if ( ! $controller = $this->getControllerForConnection( $from ) ) {
            return;
        }
        
        $response = $controller->onMessage( $from, $msg, $this->clients );
        $response?->send( $from );
    }
    
    #[Autowire]
    public function setControllers( #[Autowire( tag: RuntimeDiTags::SOCKET_CONTROLLER )] ?iterable $controllers ): void {
        if ( ! $controllers ) {
            return;
        }
        
        foreach ( $controllers as $controller ) {
            $this->controllers[ $controller::class ] = $controller;
        }
    }
    
    private function getControllerForConnection( \Swift\WebSocket\WsConnection $conn ): ?\Swift\WebSocket\Controller\WebSocketControllerInterface {
        $route = null;
        try {
            if ( ! $route = $this->socketRouter->getRouteForRequest( $conn->getRequest() ) ) {
                return null;
            }
        } catch ( NotFoundException ) {
            $this->consoleLogger->getIo()->writeln(
                sprintf(
                    'Closing connection %s, because route %s was not found',
                    $conn->getResourceId(),
                    $conn?->getRequest()?->getUri()?->getPath(),
                )
            );
            $conn->close();
        }
        
        return $this->controllers[ $route->getController() ] ?? null;
    }
    
}