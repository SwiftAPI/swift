<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration;

use RuntimeException;
use stdClass;
use Swift\Configuration\Exception\UnknownConfigurationKeyException;
use Swift\Configuration\Helper\Parser;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Attributes\DI;

/**
 * Class AppConfiguration
 * @package Swift\Configuration
 */
#[DI(tags: [DiTags::CONFIGURATION]), Autowire]
class AppConfiguration {

    /**
     * @var stdClass $scoped
     */
    private stdClass $scoped;

    /**
     * @var array $mapping
     */
    private array $mapping;

    /**
     * AppConfiguration constructor.
     *
     * @param YamlFileLoader $fileLoader
     * @param Parser $parser
     */
    public function __construct(
        private YamlFileLoader $fileLoader,
        private Parser $parser,
    ) {
        $settings = $this->parser->parseConfig($this->fileLoader->getLoadedFiles());
        $config = $settings['config'];

        $this->scoped       = $config->scoped;
        $this->mapping      = $settings['map']->scoped;
    }

    /**
     * @inheritDoc
     */
    public function getScope(): array {
        return array_keys($this->mapping);
    }

    /**
     * Load configuration from files
     */
    private function loadConfig(): void {
        $settings = $this->parser->parseConfig($this->fileLoader->getLoadedFiles());
        $config = $settings['config'];

        $this->scoped       = $config->scoped;
        $this->mapping      = $settings['map']->scoped;
    }

    /**
     * Method to get setting
     *
     * @param string $settingName
     * @param string|null $scope
     *
     * @return mixed
     */
    public function get(string $settingName, string $scope): mixed {
        if (!$this->has($settingName, $scope)) {
            throw new UnknownConfigurationKeyException(sprintf('Could not find configuration key %s with scope %s', $settingName, $scope));
        }

        $comparison = $this->scoped->{$scope};

        if (count(explode('.', $settingName)) > 1) {
            // Nested setting
            $nameArray  = explode('.', $settingName);
            if (property_exists($comparison, $nameArray[0])) {
                $setting = $comparison->{$nameArray[0]};

                return property_exists($setting, $nameArray[1]) ? $setting->{$nameArray[1]} : null;
            }

            return null;
        }

        return property_exists($comparison, $settingName) ? $comparison->{$settingName} : null;
    }

    /**
     * Update a setting with given parameters. Only existing settings can be updated. New settings can not be created
     *
     * @param mixed $value
     * @param string $identifier
     * @param string $scope
     *
     * @throws UnknownConfigurationKeyException
     */
    public function set(mixed $value, string $identifier, string $scope): void {
        if (!$this->has($identifier, $scope)) {
            throw new UnknownConfigurationKeyException('Setting undefined settings is not supported');
        }

        // Update runtime
        $comparison = $this->scoped->{$scope};
        if (count(explode('.', $identifier)) > 1) {
            // Nested setting
            $nameArray  = explode('.', $identifier);
            if (property_exists($comparison, $nameArray[0])) {
                $setting = $comparison->{$nameArray[0]};

                if (property_exists($setting, $nameArray[1])) {
                    $setting->{$nameArray[1]} = $value;
                }
            }
        } elseif (property_exists($comparison, $identifier)) {
            $comparison->{$identifier} = $value;
        }

        // Update file
        $filename = $this->mapping[$scope][$identifier];
        $settings = $this->fileLoader->parseFile($filename);
        $split = explode('.', $identifier);

        if (count($split) < 2) {
            return;
        }

        $settings[$split[0]][$split[1]] = $value;

        $this->fileLoader->setFile($filename, $settings);
    }

    public function has( string $identifier, string $scope ): bool {
        return $scope && property_exists($this->scoped, $scope);
    }
}