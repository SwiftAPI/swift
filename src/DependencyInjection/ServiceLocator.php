<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\DependencyInjection;

use ReflectionClass;
use Swift\DependencyInjection\Provider\ContainerAwareTrait;

/**
 * Class ServiceLocator
 * @package Swift\DependencyInjection
 */
class ServiceLocator implements ServiceLocatorInterface {
    
    use ContainerAwareTrait;
    
    /**
     * Retrieve a service from the container
     *
     * @template T
     *
     * @param class-string<T> $service
     *
     * @return T|null
     */
    public function get( string $service ): object|null {
        return $this->container->get( $service );
    }
    
    /**
     * Confirm whether the given service is registered as a service
     *
     * @param string $service
     *
     * @return bool
     */
    public function has( string $service ): bool {
        return $this->container->has( $service );
    }
    
    /**
     * Retrieve a service from the container by id
     *
     * @param string $serviceId
     *
     * @return object|null
     */
    public function getById( string $serviceId ): object|null {
        return null;
    }
    
    /**
     * Retrieve all services tagged by the given parameter
     *
     * @param string $tag
     *
     * @return array
     */
    public function getServicesByTag( string $tag ): array {
        return $this->container->getServicesByTag( $tag );
    }
    
    /**
     * Retrieve all service instances for given tag
     *
     * @param string $tag
     *
     * @return array
     */
    public function getServiceInstancesByTag( string $tag ): array {
        return $this->container->getServiceInstancesByTag( $tag );
    }
    
    /**
     * Get reflection by service id
     *
     * @param string $serviceId
     *
     * @return ReflectionClass
     */
    public function getReflectionClass( string $serviceId ): ReflectionClass {
        $class = $this->container->getReflectionClass( $serviceId );
        
        return $class ? $this->reflectionFactory->getReflectionClass( $class->getName() ) : $class;
    }
    
    /**
     * @inheritDoc
     */
    public function getResourcePaths(): array {
        return $this->container->getResourcePaths();
    }
    
    
}