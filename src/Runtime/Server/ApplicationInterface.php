<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Runtime\Server;


interface ApplicationInterface {
    
    /**
     * Run the application
     */
    public function run(): void;
    
    public function setServer( ServerInterface $server ): void;
    
    public function getServer(): ServerInterface;
    
    public function getStartTime(): float;
    
    public function getStartMemory(): int;
    
}