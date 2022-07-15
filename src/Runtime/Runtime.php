<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Runtime;


use React\EventLoop\LoopInterface;
use Swift\Configuration\ConfigurationInterface;
use Swift\Configuration\Utils;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Runtime\Cli\ConsoleLogger;

/**
 * Utility class for interacting with the runtime component
 */
#[Autowire]
final class Runtime implements RuntimeInterface {
    
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly ConfigurationInterface $configuration,
        private readonly ConsoleLogger $consoleLogger,
    ) {
    }
    
    public function isDebug(): bool {
        return Utils::isDebug( $this->configuration );
    }
    
    public function isDevelopment(): bool {
        return Utils::isDevMode( $this->configuration );
    }
    
    public function isEnabled(): bool {
        return $this->configuration->get( 'runtime.enabled', 'runtime' );
    }
    
    public function isRunningInCurrent(): bool {
        return defined( 'SWIFT_RUNTIME' ) && SWIFT_RUNTIME;
    }
    
    public function restart(): void {
        $this->consoleLogger->getIo()->block( '⏳ Server restarting...', 'INFO', 'fg=blue', ' ', true );
        
        $this->kernel->finalize( 97 );
    }
    
    public function pausedRestart( float $seconds ): void {
        $section = $this->consoleLogger->getCommand()->createOutputSection();
        
        $section->writeln( sprintf( '⏳ <comment>Server restarting in %ss...</comment>', $seconds ) );
        
        for ( $i = ( $seconds / 0.1 ); $i >= 0; $i -- ) {
            $section->clear( 1 );
            
            $section->writeln( sprintf( '⏳ <comment>Server restarting in %ss...</comment>', $i / 10 ) );
            
            usleep( 100000 );
        }
        $section->clear( 1 );
        
        $this->consoleLogger->getIo()->block( '⏳ Server restarting...', 'INFO', 'fg=blue', ' ', true );
        
        $this->kernel->finalize( 98 );
    }
    
    public function stop( int $code = 99 ): void {
        $this->consoleLogger->getIo()->block( '⏳ Gracefully stopping server...', 'INFO', 'fg=blue', ' ', true );
        
        $this->kernel->finalize( 99 );
    }
    
    public function getLoop(): LoopInterface {
        return $this->kernel->getLoop();
    }
    
}