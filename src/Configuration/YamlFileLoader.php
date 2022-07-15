<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration;

use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Yaml\Yaml;
use RuntimeException;

/**
 * Class YamlFileLoader
 * @package Swift\Configuration
 */
#[Autowire]
class YamlFileLoader {

    /**
     * FileLoader constructor.
     *
     * @param Yaml $yaml
     * @param array $imported paths that have been imported
     */
	public function __construct(
		private Yaml $yaml,
        private array $imported = [],
	) {
		$this->loadFiles();
	}

	/**
	 * Method to get loaded config files
	 *
	 * @return array
	 */
	public function getLoadedFiles(): array {
		return $this->imported;
	}

	/**
	 * Method to load initial config file
	 *
	 * @throws RuntimeException
	 */
	public function loadFiles() : void {
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