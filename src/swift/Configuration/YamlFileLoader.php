<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration;

use Swift\Kernel\Attributes\Autowire;
use Swift\Yaml\Yaml;
use RuntimeException;

/**
 * Class YamlFileLoader
 * @package Swift\Configuration
 */
#[Autowire]
class YamlFileLoader {

	/**
	 * @var Yaml $yaml
	 */
	private $yaml;

	/**
	 * @var array $imported paths that have been imported
	 */
	private $imported = array();

	/**
	 * FileLoader constructor.
	 *
	 * @param $yaml
	 */
	public function __construct(
		Yaml $yaml
	) {
		$this->yaml = $yaml;

		$this->loadFiles();
	}

	/**
	 * Method to get loaded config files
	 *
	 * @return array
	 */
	public function getLoadedFiles() : array {
		return $this->imported;
	}

	/**
	 * Method to load initial config file
	 *
	 * @throws RuntimeException
	 */
	public function loadFiles() : void {
		// General config
		$path   = INCLUDE_DIR . '/config.yaml';
		if (!$this->fileExists($path)) {
			throw new RuntimeException($path . ' does not exist', 500);
		}
		$config = $this->yaml->parseFile($path, 4);

		$this->imported[$path]  = $config;

		if (!empty($config['imports'])) {
			$this->parseSubConfig($config['imports']);
		}

		// Framework config
		$path   = INCLUDE_DIR . '/vendor/henrivantsant/swift/config.yaml';
		$fallback = INCLUDE_DIR . '/src/swift/config.yaml';
		if (!$this->fileExists($path) && !$this->fileExists($fallback)) {
			throw new RuntimeException($path . ' does not exist', 500);
		}
		$path = $this->fileExists($path) ? $path : $fallback;
		$config = $this->yaml->parseFile($path, 4);

		$this->imported[$path]  = $config;

		if (!empty($config['imports'])) {
			$this->parseSubConfig($config['imports']);
		}

		// App config
		$path   = INCLUDE_DIR . '/app/config.yaml';
		if ($this->fileExists($path)) {
			$config = $this->yaml->parseFile($path, 4);
			$this->imported[$path] = $config;

			if (!empty($config['imports'])) {
				$this->parseSubConfig($config['imports']);
			}
		}
	}

	/**
	 * Method to parse a subconfig file
	 *
	 * @param array $imports
	 *
	 * @throws RuntimeException
	 */
	public function parseSubConfig(array $imports) : void {
		foreach ($imports as $import) {
			$subconfigPath = INCLUDE_DIR . '/' . $import['resource'];
			if (!$this->fileExists($subconfigPath)) {
				throw new RuntimeException($subconfigPath . ' not found', 500);
			}
			$subconfig = $this->yaml->parseFile($subconfigPath);
			$this->imported[$subconfigPath] = $subconfig;
			if (!empty($subconfig['imports'])) {
				$this->parseSubConfig($subconfig['imports']);
			}
		}
	}

	/**
	 * Method to validate a file path
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	private function fileExists(string $path) : bool {
		return is_readable($path) && file_exists($path) && is_file($path) && is_writable($path);
	}

    /**
     * Parse filename to yaml file to array
     *
     * @param string $filename
     *
     * @return array
     * @throws RuntimeException
     */
    public function parseFile(string $filename): array {
        if (!$this->fileExists($filename)) {
            throw new RuntimeException($filename . ' does not exist', 500);
        }

        return $this->yaml->parseFile($filename, 4);
	}

    /**
     * Set yaml file with given contents
     *
     * @param string $filename
     * @param array $content
     *
     * @throws RuntimeException
     */
    public function setFile(string $filename, array $content): void {
        if (!$this->fileExists($filename)) {
            throw new RuntimeException($filename . ' does not exist', 500);
        }

        file_put_contents($filename, $this->yaml->dump($content, 2));
	}

}