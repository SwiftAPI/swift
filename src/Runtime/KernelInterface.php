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
use Swift\Console\Style\ConsoleStyle;
use Swift\Runtime\Cli\AbstractRuntimeCommand;

interface KernelInterface {
    
    public function run( ConsoleStyle $io, AbstractRuntimeCommand $command ): int;
    
    public function isDebug(): bool;
    
    public function finalize( int $code ): void;
    
    public function getLoop(): LoopInterface;
    
    
}