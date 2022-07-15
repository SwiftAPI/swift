<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Application;

require_once 'globals-and-includes.php';

use Exception;
use Swift\Application\Bootstrap\Bootstrap;
use Swift\Kernel\KernelInterface;

/**
 * Class Application
 * @package Swift\Application
 */
class Application implements ApplicationInterface {
    
    private float $startTime;
    private int $startMemory;
    private ?\Swift\Kernel\KernelInterface $kernel;
    
    /**
     * @param \Swift\Kernel\KernelInterface|null $kernel
     */
    public function __construct(
        ?\Swift\Kernel\KernelInterface $kernel = null
    ) {
        $this->kernel = $kernel ?: Bootstrap::createKernel();
    
        // Saves the start time and memory usage.
        $this->startTime   = microtime( true );
        $this->startMemory = memory_get_usage();
    }
    
    /**
     * Method to run application
     *
     * @throws Exception
     */
    public function run(): void {
        $this->kernel->run();
    }
    
    public function getKernel(): KernelInterface {
        return $this->kernel;
    }
    
    public function setKernel( KernelInterface $kernel ): void {
        $this->kernel = $kernel;
    }
    
    public function getStartTime(): float {
        return $this->startTime;
    }
    
    public function getStartMemory(): int {
        return $this->startMemory;
    }
    
    
}