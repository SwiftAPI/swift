<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem\Watcher;


use Swift\DependencyInjection\Attributes\DI;
use Swift\FileSystem\Watcher\Config\WatchList;
use Swift\FileSystem\Watcher\ResourceWatcherBased\ChangesListener;

#[DI( autowire: false )]
final class Watcher {
    
    public function __construct(
        private readonly ChangesListener $filesystemListener,
    ) {
    }
    
    public function startWatching(
        ProcessRunner $processRunner,
        WatchList     $watchList,
        float         $delayToRestart
    ): void {
        $processRunner->start();
        
        $this->filesystemListener->start( $watchList );
        
        $this->filesystemListener->onChange(
            static function ( array $args ) use ( $processRunner, $delayToRestart ): void {
                /** @var \Swift\FileSystem\Watcher\ResourceWatcherBased\ResourceWatcherResult $changes */
                ['changes' => $changes] = $args;
                
                $processRunner->restart( $delayToRestart, $changes );
            }
        );
    }
    
}