<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\DependencyInjection;

use Swift\Code\ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;


interface ContainerInterface extends SymfonyContainerInterface {
    
    /**
     * Method to get classes by tag
     *
     * @param string $tag
     *
     * @return array
     */
    public function getServicesByTag(string $tag): array;
    
    /**
     * Get all service instances for given tag
     *
     * @param string $tag
     *
     * @return array
     * @throws \Exception
     */
    public function getServiceInstancesByTag( string $tag ): array;
    
    /**
     * @return array
     */
    public function getResourcePaths(): array;
    
    /**
     * Retrieves the requested reflection class and registers it for resource tracking.
     *
     * @throws \ReflectionException when a parent class/interface/trait is not found and $throw is true
     *
     * @final
     */
    public function getReflectionClass(?string $class, bool $throw = true): ?\ReflectionClass;
    
    
}