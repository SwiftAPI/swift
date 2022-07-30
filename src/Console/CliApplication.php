<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Console;

require_once 'globals-and-includes.php';


final class CliApplication implements ApplicationInterface {
    
    private float $startTime;
    private int $startMemory;
    private ?KernelInterface $kernel;
    
    public function __construct(
        ?KernelInterface $kernel = null
    ) {
        $this->kernel = $kernel ?: Bootstrap::createKernel();
    
        // Saves the start time and memory usage.
        $this->startTime   = microtime( true );
        $this->startMemory = memory_get_usage();
    }
    
    /**
     * Bootstrap CLI Application
     */
    public function run(): void {
        if (PHP_SAPI !== 'cli') {
            echo 'bin/console must be run as a CLI application';
            exit(1);
        }
        
        $this->kernel->run();
    }
    
    public function setKernel( KernelInterface $kernel ): void {
        $this->kernel = $kernel;
    }
    
    public function getKernel(): KernelInterface {
        return $this->kernel;
    }
    
    public function getStartTime(): float {
        return $this->startTime;
    }
    
    public function getStartMemory(): int {
        return $this->startMemory;
    }
    
}