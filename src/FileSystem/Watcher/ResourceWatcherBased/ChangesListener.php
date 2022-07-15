<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem\Watcher\ResourceWatcherBased;


use React\EventLoop\LoopInterface;
use Swift\DependencyInjection\Attributes\DI;
use Swift\FileSystem\Watcher\Config\WatchList;

#[DI( autowire: false )]
final class ChangesListener implements \Swift\FileSystem\Watcher\ChangeListenerInterface {
    
    private const INTERVAL = 0.15;
    
    private array $callbacks = [];
    
    public function __construct(
        private readonly LoopInterface $loop
    ) {
    }
    
    public function start( WatchList $watchList ): void {
        $watcher = ResourceWatcherBuilder::create( $watchList );
        
        $this->loop->addPeriodicTimer(
            self::INTERVAL,
            function () use ( $watcher ): void {
                $changes = $watcher->findChanges();
                if ( $changes->hasChanges() ) {
                    $this->emit( 'change', ['changes' => $changes] );
                }
            }
        );
    }
    
    private function on( string $event, callable $callback ): void {
        if ( ! array_key_exists( $event, $this->callbacks ) ) {
            $this->callbacks[ $event ] = [];
        }
        
        $this->callbacks[ $event ][] = $callback;
    }
    
    private function emit( string $event, array $args ): void {
        if ( ! array_key_exists( $event, $this->callbacks ) ) {
            return;
        }
        
        foreach ( $this->callbacks[ $event ] as $callback ) {
            $callback( $args );
        }
    }
    
    public function onChange( callable $callback ): void {
        $this->on( 'change', $callback );
    }
    
}