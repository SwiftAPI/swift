<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Console;


use Swift\DependencyInjection\ContainerInterface;

interface KernelInterface {
    
    public function run(): int;
    
    public function isDebug(): bool;
    
    public function finalize(): void;
    
    public function setContainer( ContainerInterface $container ): void;
    
    public function getContainer(): ContainerInterface;
    
}