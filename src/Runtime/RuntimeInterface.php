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

interface RuntimeInterface {
    
    public function isDebug(): bool;
    
    public function isDevelopment(): bool;
    
    public function restart(): void;
    
    public function pausedRestart( float $seconds ): void;
    
    public function stop( int $code = 99 ): void;
    
    public function getLoop(): LoopInterface;
    
}