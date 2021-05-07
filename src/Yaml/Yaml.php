<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Yaml;

use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

/**
 * Class Yaml
 * @package Swift\Yaml
 */
class Yaml {

	/**
	 * Parses a YAML file into a PHP value.
	 *
	 * Usage:
	 *
	 *     $array = Yaml::parseFile('config.yml');
	 *     print_r($array);
	 *
	 * @param string $filename The path to the YAML file to be parsed
	 * @param int    $flags    A bit field of PARSE_* constants to customize the YAML parser behavior
	 *
	 * @return mixed The YAML converted to a PHP value
	 *
	 * @throws ParseException If the file could not be read or the YAML is not valid
	 */
	public function parseFile(string $filename, int $flags = 0)
	{
		$yaml = new Parser();

		return $yaml->parseFile($filename, $flags);
	}

	/**
	 * Parses YAML into a PHP value.
	 *
	 *  Usage:
	 *  <code>
	 *   $array = Yaml::parse(file_get_contents('config.yml'));
	 *   print_r($array);
	 *  </code>
	 *
	 * @param string $input A string containing YAML
	 * @param int    $flags A bit field of PARSE_* constants to customize the YAML parser behavior
	 *
	 * @return mixed The YAML converted to a PHP value
	 *
	 * @throws ParseException If the YAML is not valid
	 */
	public function parse(string $input, int $flags = 0): mixed
	{
		$yaml = new Parser();

		return $yaml->parse($input, $flags);
	}

    /**
     * Dumps a PHP value to a YAML string.
     *
     * The dump method, when supplied with an array, will do its best
     * to convert the array into friendly YAML.
     *
     * @param mixed $input The PHP value
     * @param int $inline The level where you switch to inline YAML
     * @param int $indent The amount of spaces to use for indentation of nested nodes
     * @param int $flags A bit field of DUMP_* constants to customize the dumped YAML string
     *
     * @return string A YAML string representing the original PHP value
     */
	public function dump( mixed $input, int $inline = 2, int $indent = 4, int $flags = 0 ): string
	{
		$yaml = new Dumper($indent);

		return $yaml->dump($input, $inline, 0, $flags);
	}
}