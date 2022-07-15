<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration;

use Swift\Configuration\Exception\InvalidConfigurationValueException;
use Swift\Configuration\Exception\UnknownConfigurationKeyException;

/**
 * Interface ConfigurationInterface
 * @package Swift\Configuration\Definition
 */
interface ConfigurationInterface  {

    /**
     * Get configuration value
     *
     * @param string $identifier
     * @param string $scope
     *
     * @return mixed
     *
     * @throws UnknownConfigurationKeyException
     */
    public function get( string $identifier, string $scope ): mixed;

    /**
     * Set configuration value
     *
     * @param mixed $value
     * @param string $identifier
     * @param string $scope
     *
     * @return void
     *
     * @throws InvalidConfigurationValueException
     * @throws UnknownConfigurationKeyException
     */
    public function set( mixed $value, string $identifier, string $scope ): void;

    /**
     * Determine whether configuration keys exists
     *
     * @param string $identifier
     * @param string $scope
     *
     * @return bool
     */
    public function has( string $identifier, string $scope ): bool;
    
    /**
     * Persist configuration changes
     */
    public function persist(): void;

}