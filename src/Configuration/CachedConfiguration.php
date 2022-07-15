<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration;

use Swift\Configuration\Exception\UnknownConfigurationKeyException;
use Swift\DependencyInjection\Attributes\DI;

/**
 * Class Configuration
 * @package Swift\Configuration
 */
#[DI(autowire: false)]
class CachedConfiguration implements ConfigurationInterface {

    /** @var ConfigurationScope[] $configs */
    private array $configs;
    
    /**
     * @param \Swift\Configuration\ConfigurationScope[] $configs
     */
    public function __construct( array $configs ) {
        $this->configs = $configs;
    }
    
    /**
     * @inheritDoc
     */
    public function get( string $identifier, string $scope ): mixed {
        if (!$this->has($identifier, $scope)) {
            throw new UnknownConfigurationKeyException(sprintf('Could not find registered configuration for scope %s', $scope));
        }

        return $this->configs[$scope]->get($identifier, $scope);
    }

    /**
     * @inheritDoc
     */
    public function set( mixed $value, string $identifier, string $scope ): void {
        if (!$this->has($identifier, $scope)) {
            throw new UnknownConfigurationKeyException(sprintf('Could not find registered configuration for scope %s', $scope));
        }

        $this->configs[$scope]->set($value, $identifier, $scope);
    }

    /**
     * @inheritDoc
     */
    public function has( string $identifier, ?string $scope = null ): bool {
        if (!array_key_exists($scope, $this->configs)) {
            return false;
        }

        return $this->configs[$scope]->has($identifier, $scope);
    }
    
    public function persist(): void {
        foreach ($this->configs as $config) {
            $config->persist();
        }
    }
    
    
}