<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel;

use ReflectionClass;
use Swift\Kernel\Container\Container;

/**
 * Class ServiceLocator
 * @package Swift\Kernel
 */
class ServiceLocator implements ServiceLocatorInterface {

    use ContainerAwareTrait;

    /**
     * Retrieve a service from the container
     *
     * @param string $service
     *
     * @return object|null
     */
    public function get( string $service ): object|null {
        return $this->container->get($service);
    }

    /**
     * Confirm whether the given service is registered as a service
     *
     * @param string $service
     *
     * @return bool
     */
    public function has( string $service ): bool {
        return $this->container->has($service);
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
        return $this->container->getServicesByTag($tag);
    }

    /**
     * Retrieve all service instances for given tag
     *
     * @param string $tag
     *
     * @return array
     */
    public function getServiceInstancesByTag( string $tag ): array {
        return $this->container->getServiceInstancesByTag($tag);
    }

    /**
     * Get reflection by service id
     *
     * @param string $serviceId
     *
     * @return ReflectionClass
     */
    public function getReflectionClass( string $serviceId ): ReflectionClass {
        return $this->container->getReflectionClass($serviceId);
    }

}