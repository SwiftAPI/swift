<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration;

use Swift\Configuration\Exception\UnknownConfigurationKeyException;
use Swift\Kernel\Attributes\Autowire;
use Swift\Yaml\Yaml;

/**
 * Class Configuration
 * @package Swift\Configuration
 */
#[Autowire]
class Configuration implements ConfigurationInterface {

    /** @var ConfigurationScope[] $configs */
    private array $configs = array();

    /**
     * DevConfiguration constructor.
     *
     * @param Yaml $yaml
     */
    public function __construct(
        private Yaml $yaml,
    ) {
    }

    /**
     * @param iterable|null $configs
     */
    #[Autowire]
    public function setConfiguration( #[Autowire(tag: DiTags::CONFIGURATION)] ?iterable $configs ): void {
        foreach($configs as /** @var ConfigurationInterface */$config) {
            $scope = $config->getScope();
            if (is_array($scope)) {
                foreach ($scope as $item) {
                    if (array_key_exists($item, $this->configs)) {
                        throw new \InvalidArgumentException('Duplicate configuration scope is not possible');
                    }
                    $this->configs[$item] = $config;
                }
            } else {
                if (array_key_exists($scope, $this->configs)) {
                    throw new \InvalidArgumentException('Duplicate configuration scope is not possible');
                }
                $this->configs[$scope] = $config;
            }
        }
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


}