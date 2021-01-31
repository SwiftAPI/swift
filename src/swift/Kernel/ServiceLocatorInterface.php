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

/**
 * Interface ServiceLocatorInterface
 * @package Swift\Kernel
 */
interface ServiceLocatorInterface {

    /**
     * Retrieve a service from the container
     *
     * @param string $service
     *
     * @return object|null
     */
    public function get( string $service ): object|null;

    /**
     * Confirm whether the given service is registered as a service
     *
     * @param string $service
     *
     * @return bool
     */
    public function has( string $service ): bool;

    /**
     * Retrieve a service from the container by id
     *
     * @param string $serviceId
     *
     * @return object|null
     */
    public function getById( string $serviceId ): object|null;

    /**
     * Retrieve all services tagged by the given parameter
     *
     * @param string $tag
     *
     * @return array
     */
    public function getServicesByTag( string $tag ): array;

    /**
     * Retrieve all service instances for given tag
     *
     * @param string $tag
     *
     * @return array
     */
    public function getServiceInstancesByTag( string $tag ): array;

    /**
     * Get reflection by service id
     *
     * @param string $serviceId
     *
     * @return ReflectionClass
     */
    public function getReflectionClass( string $serviceId ): ReflectionClass;

}