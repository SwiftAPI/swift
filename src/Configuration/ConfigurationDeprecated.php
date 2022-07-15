<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration;

use stdClass;
use Swift\Configuration\Helper\Parser;
use RuntimeException;
use Swift\DependencyInjection\Attributes\Autowire;

/**
 * Class ConfigurationDeprecated
 * @package Swift\Configuration
 */
#[Autowire]
class ConfigurationDeprecated {

	private stdClass $database;

	/**
	 * @var stdClass $routing
	 */
	private $routing;

	/**
	 * @var stdClass $app
	 */
	private $app;

	/**
	 * @var stdClass $scoped
	 */
	private $scoped;

	/**
	 * @var stdClass $general
	 */
	private $general;

    /**
     * @var array $mapping
     */
	private $mapping;

    /**
     * Configuration constructor.
     *
     * @param YamlFileLoader $fileLoader
     * @param Parser $parser
     */
	public function __construct(
		private YamlFileLoader $fileLoader,
		private Parser $parser,
	) {
		$this->loadConfig();
	}

    /**
     * Load configuration from files
     *
     * @throws \Exception
     */
    private function loadConfig(): void {
        $settings = $this->parser->parseConfig($this->fileLoader->getLoadedFiles());
        $config = $settings['config'];

        if (!isset($config->general->database)) {
            throw new \Exception('Database configuration missing', 500);
        }
        if (!isset($config->general->routing)) {
            throw new \Exception('Routing configuration missing', 500);
        }
        if (!isset($config->general->app)) {
            throw new \Exception('Application configuration missing', 500);
        }

        $this->database     = $config->general->database;
        $this->routing      = $config->general->routing;
        $this->app          = $config->general->app;
        $this->general      = $config->general;
        $this->scoped       = $config->scoped;
        $this->mapping      = $settings['map'];
	}

    /**
     * Method to get setting
     *
     * @param string $settingName
     * @param string|null $scope
     *
     * @return mixed
     */
	public function get(string $settingName, ?string $scope = null): mixed {
	    $scope ??= 'app';
	    return $this->devConfiguration->get($settingName, $scope);

	    $scope = is_null($scope) ? '' : $scope;
		if ($scope && !property_exists($this->scoped, $scope)) {
			return null;
		}

		$comparison = $scope ? $this->scoped->{$scope} : $this;

		if (count(explode('.', $settingName)) > 1) {
			// Nested setting
			$nameArray  = explode('.', $settingName);
			if (property_exists($comparison, $nameArray[0])) {
				$setting = $comparison->{$nameArray[0]};
				unset($nameArray[0]);

				foreach ($nameArray as $item) {
					if (property_exists($setting, $item)) {
						$setting = $setting->{$item};
					} else {
						return null;
					}
				}

				return $setting;
			} else {
				return null;
			}
		} else {
            return property_exists($comparison, $settingName) ? $comparison->{$settingName} : null;
		}
	}

    /**
     * Update a setting with given parameters. Only existing settings can be updated. New settings can not be created
     *
     * @param string $settingName
     * @param $value
     * @param string|null $scope
     *
     * @throws RuntimeException
     */
    public function set(string $settingName, $value, ?string $scope = null): void {
        if (is_null($this->get($settingName, $scope))) {
            throw new RuntimeException('Setting undefined settings is not supported');
        }

        $filename = !is_null($scope) ? $this->mapping->scoped[$scope][$settingName] : $this->mapping->general[$settingName];
        $settings = $this->fileLoader->parseFile($filename);
        $split = explode('.', $settingName);

        if (count($split) < 2) {
            return;
        }

        $settings[$split[0]][$split[1]] = $value;

        $this->fileLoader->setFile($filename, $settings);
        
        // Reload config after file change
        $this->loadConfig();
	}

}