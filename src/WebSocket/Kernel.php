<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\WebSocket;


use React\EventLoop\LoopInterface;
use React\Socket\SocketServer;
use Swift\Configuration\ConfigurationInterface;
use Swift\Configuration\Utils;
use Swift\Console\Style\ConsoleStyle;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Events\EventDispatcherInterface;
use Swift\Runtime\Cli\AbstractRuntimeCommand;
use Swift\Runtime\RuntimeKernelInterface;
use Swift\WebSocket\Router\SocketRouter;
use Swift\WebSocket\Server\HttpServer;
use Swift\WebSocket\Server\IoServer;
use Swift\WebSocket\Server\WsServer;

#[Autowire]
class Kernel implements RuntimeKernelInterface {
    
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected ConfigurationInterface $configuration,
        protected MessageComponent $messageComponent,
        protected SocketRouter $router,
    ) {
    }
    
    public function run( ConsoleStyle $io, AbstractRuntimeCommand $command, LoopInterface $loop ): int {
        $io->title( 'Websocket server' );
        $section = $command->createOutputSection();
        
        $server = new IoServer(
            new HttpServer(
                new WsServer(
                    $this->router,
                    $this->messageComponent,
                )
            ),
            new SocketServer( '0.0.0.0' . ':' . $this->configuration->get('websocket.port', 'runtime'), [], $loop ),
            $loop,
        );
        
        // Boot router
        $section->writeln( '⏳ <fg=blue>Booting socket router...</>' );
        $this->router->compile();
        $section->clear( 1 );
        $section->writeln( '✅ <fg=green;options=bold> Successfully booted socket router</>' );
        
        $io->newLine( 1 );
        
        $io->horizontalTable(
            [
                'Status',
                'Port',
                'Routes',
            ],
            [
                [
                    'Enabled',
                    $this->configuration->get('websocket.port', 'runtime'),
                    $this->router->getRoutes()->count(),
                ],
            ],
        );
    
        $io->newLine( 3 );
        
        $server->run();
        
        
        return 1;
    }
    
    
    public function isDebug(): bool {
        return Utils::isDebug( $this->configuration );
    }
    
    public function finalize(): void {
    
    }
    
    

    
}