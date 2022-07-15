<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem\Watcher;


use React\EventLoop\LoopInterface;
use Swift\DependencyInjection\Attributes\DI;
use Swift\FileSystem\Watcher\ResourceWatcherBased\ResourceWatcherResult;
use Swift\FileSystem\Watcher\Screen\Screen;
use Swift\Runtime\RuntimeInterface;

#[DI( autowire: false )]
final class ProcessRunner {
    
    public function __construct(
        private readonly LoopInterface $loop,
        private readonly Screen $screen,
        private readonly RuntimeInterface $runtime,
    ) {
    }
    
    public function start(): void {
        $this->screen->showSpinner( $this->loop );
    }
    
    public function stop(): void {
        $this->runtime->stop();
    }
    
    public function restart( float $delayToRestart, ResourceWatcherResult $changes ): void {
        $this->screen->restarting( $changes );
        $this->runtime->pausedRestart( $delayToRestart );
    }
    
}