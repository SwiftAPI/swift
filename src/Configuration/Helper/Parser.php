<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration\Helper;

use JetBrains\PhpStorm\ArrayShape;
use stdClass;

/**
 * Class Parser
 * @package Swift\Configuration\Helper
 */
class Parser {

	/**
	 * Method to parse array of config files to object
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	#[ArrayShape( [ 'config' => stdClass::class, 'map' => stdClass::class ] )]
    public function parseConfig( array $config): array {
		$configuration  = new stdClass();
		$configuration->general = new stdClass();
		$configuration->scoped  = new stdClass();
		$map = new stdClass();
		$map->general = array();
		$map->scoped = array();

		foreach ($config as $scopePath => $items) {
			$scope  = $this->parseScope($scopePath);

			foreach ($items as $category => $item) {
				if ($category === 'imports') {
					continue;
				}
				if ($scope->area !== 'app') {
					$configuration->general = $this->appendSettings($configuration->general, $category, $item);
					$map->general = $this->appendMapping($map->general, $category, $item, $scopePath);
				}

				if (!property_exists($configuration->scoped, $scope->scope)) {
					$configuration->scoped->{$scope->scope} = new stdClass();
				}
				if (!array_key_exists($scope->scope, $map->scoped)) {
				    $map->scoped[$scope->scope] = array();
                }
				$configuration->scoped->{$scope->scope} = $this->appendSettings($configuration->scoped->{$scope->scope}, $category, $item);
				$map->scoped[$scope->scope] = $this->appendMapping($map->scoped[$scope->scope], $category, $item, $scopePath);
			}
		}

		return array(
		    'config'    => $configuration,
            'map'       => $map,
        );
	}

	/**
	 * Method to parse scope data
	 *
	 * @param string $scopePath
	 *
	 * @return stdClass
	 */
	public function parseScope(string $scopePath) : stdClass {
        $scopeArea = str_replace( array( INCLUDE_DIR, 'config.yaml', 'tokens.yaml' ), '', $scopePath );

		$scope  = new stdClass();
		$scope->path    = $scopePath;
		$scope->name    = $scopeArea === '/' ? 'root' : trim($scopeArea, '/');
		$scope->name    = str_starts_with( $scope->name, 'vendor/swift' ) ? 'framework' : $scope->name;
		$scope->name    = str_starts_with( $scope->name, 'src/swift' ) ? 'framework' : $scope->name;
		$scope->scope   = $scope->name;

		$explode = explode('/', $scope->scope);
		$scope->area    = is_array($explode) && count($explode) ? $explode[0] : 'root';

		return $scope;
	}

	/**
	 * Method to parse settings mapping
	 *
	 * @param stdClass $baseClass
	 * @param string    $settingName
	 * @param array $settings
	 *
	 * @return stdClass
	 */
	private function appendSettings( stdClass $baseClass, string $settingName, $settings) : stdClass {
        if (!property_exists($baseClass, $settingName)) {
            $baseClass->{$settingName}  = new stdClass();
        }

        if (empty($settings) || !is_array($settings)) {
            return $baseClass;
        }

        foreach ($settings as $name => $value) {
            $baseClass->{$settingName}->{$name} = $value;
        }

        return $baseClass;
	}

    /**
     * Method to append settings
     *
     * @param stdClass $baseClass
     * @param string    $settingName
     * @param array $settings
     *
     * @return stdClass
     */
    private function appendMapping(array $base, string $settingName, $settings, string $scopePath) : array {
        if (empty($settings) || !is_array($settings)) {
            return $base;
        }
        foreach ($settings as $name => $value) {
            $base[$settingName . '.' . $name] = $scopePath;
        }

        return $base;
    }
}