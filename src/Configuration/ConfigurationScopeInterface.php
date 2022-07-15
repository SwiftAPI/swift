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
use Swift\DependencyInjection\Attributes\DI;

/**
 * Interface ConfigurationScopeInterface
 * @package Swift\Configuration\Definition
 */
#[DI(tags: [DiTags::CONFIGURATION])]
interface ConfigurationScopeInterface  {

    /**
     * Scope identifier for config
     *
     * @return string|array
     */
    public function getScope(): string|array;

    /**
     * Get configuration value
     *
     * @param string $name
     * @param string $scope
     *
     * @return mixed
     *
     * @throws UnknownConfigurationKeyException
     */
    public function get( string $name, string $scope ): mixed;

    /**
     * Set configuration value
     *
     * @param mixed $value
     * @param string $name
     *
     * @return void
     *
     * @throws InvalidConfigurationValueException|UnknownConfigurationKeyException
     */
    public function set( mixed $value, string $name ): void;

    /**
     * Determine whether config has given identifier
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function has( string $identifier ): bool;

}