<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Runtime\Server;

require_once 'globals-and-includes.php';


/**
 * Class RuntimeApplication
 * @package Swift\Runtime\Server
 */
final class RuntimeApplication implements ApplicationInterface {
    
    private float $startTime;
    private int $startMemory;
    private ?ServerInterface $server;
    
    public function __construct(
        ?ServerInterface $server = null
    ) {
        $this->server = $server ?: Bootstrap::createServer();
    
        // Saves the start time and memory usage.
        $this->startTime   = microtime( true );
        $this->startMemory = memory_get_usage();
    }
    
    /**
     * Bootstrap CLI Application
     */
    public function run(): void {
        if (PHP_SAPI !== 'cli') {
            echo 'bin/server must be run as a CLI application';
            exit(1);
        }
        
        $this->server->run();
    }
    
    public function setServer( ServerInterface $server ): void {
        $this->server = $server;
    }
    
    public function getServer(): ServerInterface {
        return $this->server;
    }
    
    public function getStartTime(): float {
        return $this->startTime;
    }
    
    public function getStartMemory(): int {
        return $this->startMemory;
    }
    
}