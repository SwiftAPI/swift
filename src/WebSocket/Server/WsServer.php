<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\WebSocket\Server;


use GuzzleHttp\Psr7\Message;
use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\CloseResponseTrait;
use Ratchet\RFC6455\Handshake\RequestVerifier;
use Ratchet\RFC6455\Handshake\ServerNegotiator;
use Ratchet\RFC6455\Messaging\CloseFrameChecker;
use Ratchet\RFC6455\Messaging\Frame;
use Ratchet\RFC6455\Messaging\FrameInterface;
use Ratchet\RFC6455\Messaging\MessageBuffer;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\ConnContext;
use Ratchet\WebSocket\WsServerInterface;
use React\EventLoop\LoopInterface;
use Swift\WebSocket\MessageComponent;
use Swift\WebSocket\Router\SocketRouter;
use Swift\WebSocket\WsConnection;

class WsServer extends \Ratchet\WebSocket\WsServer {
    
    use CloseResponseTrait;
    
    /**
     * Decorated component
     * @var \Swift\WebSocket\MessageComponent
     */
    protected MessageComponent $delegate;
    
    /**
     * @var \Ratchet\RFC6455\Messaging\CloseFrameChecker
     */
    protected CloseFrameChecker $closeFrameChecker;
    
    /**
     * @var \Ratchet\RFC6455\Handshake\ServerNegotiator
     */
    protected ServerNegotiator $handshakeNegotiator;
    
    /**
     * @var \Closure
     */
    protected \Closure $ueFlowFactory;
    
    /**
     * @var \Closure
     */
    protected \Closure $pongReceiver;
    
    /**
     * @var \Closure
     */
    protected \Closure $msgCb;
    
    /**
     * @param \Swift\WebSocket\Router\SocketRouter $router
     * @param \Swift\WebSocket\MessageComponent    $component Your application to run with WebSockets
     *
     * @throws \Exception
     * @note If you want to enable sub-protocols have your component implement WsServerInterface as well
     */
    public function __construct(
        protected SocketRouter $router,
        MessageComponent              $component,
    ) {
        $this->msgCb = function(ConnectionInterface $conn, MessageInterface $msg) {
            $this->delegate->onMessage($conn, $msg);
        };
        
        if (bin2hex('✓') !== 'e29c93') {
            throw new \DomainException('Bad encoding, unicode character ✓ did not match expected value. Ensure charset UTF-8 and check ini val mbstring.func_autoload');
        }
        
        $this->delegate    = $component;
        $this->connections = new \SplObjectStorage;
        
        $this->closeFrameChecker   = new CloseFrameChecker();
        $this->handshakeNegotiator = new ServerNegotiator(new RequestVerifier());
        $this->handshakeNegotiator->setStrictSubProtocolCheck(true);
        
        if ($component instanceof WsServerInterface) {
            $this->handshakeNegotiator->setSupportedSubProtocols($component->getSubProtocols());
        }
        
        $this->pongReceiver = static function() {};
        
        $reusableUnderflowException = new \UnderflowException();
        $this->ueFlowFactory = static function() use ($reusableUnderflowException) {
            return $reusableUnderflowException;
        };
    }
    
    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
        if (null === $request) {
            throw new \UnexpectedValueException('$request can not be null');
        }
        
        $conn->httpRequest = $request;
        
        $conn->WebSocket            = new \StdClass();
        $conn->WebSocket->closing   = false;
    
        $wsConn = new WsConnection( $conn, $request );
        
        $response = $this->handshakeNegotiator->handshake($request)->withHeader('X-Powered-By', 'SWIFT');
        
        $conn->send(Message::toString($response));
        
        if (101 !== $response->getStatusCode()) {
            return $conn->close();
        }
        
        $streamer = new MessageBuffer(
            $this->closeFrameChecker,
            function(MessageInterface $msg) use ($wsConn) {
                $cb = $this->msgCb;
                $cb($wsConn, $msg);
            },
            function(FrameInterface $frame) use ($wsConn) {
                $this->onControlFrame($frame, $wsConn);
            },
            true,
            $this->ueFlowFactory
        );
        
        $this->connections->attach($conn, new ConnContext($wsConn, $streamer));
        
        return $this->delegate->onOpen( $wsConn );
    }
    
    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        if ($from->WebSocket->closing) {
            return;
        }
        
        $this->connections[$from]->buffer->onData($msg);
    }
    
    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $conn) {
        if ($this->connections->contains($conn)) {
            $context = $this->connections[$conn];
            $this->connections->detach($conn);
            
            $this->delegate->onClose($context->connection);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        if ($this->connections->contains($conn)) {
            $this->delegate->onError(
                $this->connections[$conn]->connection,
                new \Swift\WebSocket\Exceptions\WebsocketErrorException(
                    $e->getMessage(),
                    $e->getCode(),
                    $e,
                ),
            );
        } else {
            $conn->close();
        }
    }
    
    public function onControlFrame(FrameInterface $frame, WsConnection|\Ratchet\WebSocket\WsConnection $conn) {
        switch ($frame->getOpCode()) {
            case Frame::OP_CLOSE:
                $conn->close($frame);
                break;
            case Frame::OP_PING:
                $conn->send(new Frame($frame->getPayload(), true, Frame::OP_PONG));
                break;
            case Frame::OP_PONG:
                $pongReceiver = $this->pongReceiver;
                $pongReceiver($frame, $conn);
                break;
        }
    }
    
    public function setStrictSubProtocolCheck($enable) {
        $this->handshakeNegotiator->setStrictSubProtocolCheck($enable);
    }
    
    public function enableKeepAlive(LoopInterface $loop, $interval = 30) {
        $lastPing = new Frame( uniqid( '', true ), true, Frame::OP_PING);
        $pingedConnections = new \SplObjectStorage;
        $splClearer = new \SplObjectStorage;
        
        $this->pongReceiver = function(FrameInterface $frame, $wsConn) use ($pingedConnections, &$lastPing) {
            if ($frame->getPayload() === $lastPing->getPayload()) {
                $pingedConnections->detach($wsConn);
            }
        };
        
        $loop->addPeriodicTimer((int)$interval, function() use ($pingedConnections, &$lastPing, $splClearer) {
            foreach ($pingedConnections as $wsConn) {
                $wsConn->close();
            }
            $pingedConnections->removeAllExcept($splClearer);
            
            $lastPing = new Frame( uniqid( '', true ), true, Frame::OP_PING);
            
            foreach ($this->connections as $key => $conn) {
                $wsConn  = $this->connections[$conn]->connection;
                
                $wsConn->send($lastPing);
                $pingedConnections->attach($wsConn);
            }
        });
    }
    
    /**
     * @return \Psr\Http\Message\RequestInterface
     */
    public function getCurrentRequest(): RequestInterface {
        return $this->currentRequest;
    }
    
}