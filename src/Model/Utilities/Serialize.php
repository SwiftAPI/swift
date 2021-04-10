<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Utilities;


use Dibi\DateTime;
use Exception;
use stdClass;
use Swift\Kernel\Attributes\Autowire;

/**
 * Class Serialize
 * @package Swift\Model\Utilities
 */
#[Autowire]
final class Serialize {

	/**
	 * Translate constructor.
	 */
	public function __construct() { }

	/**
	 * @param      $object
	 * @param bool $toDb
	 *
	 * @return stdClass|string
	 * @throws Exception
	 */
	public function json($object, bool $toDb) {
		if ($toDb) {
			return $this->jsonToString($object);
		} else {
			return $this->jsonToObject($object);
		}
	}

	/**
	 * Method to translate a class to json object prior to db save
	 *
	 * @param mixed $stdClass
	 *
	 * @return string
	 */
	public function jsonToString($stdClass) : string {
	    $json = json_encode($stdClass);
		return !is_bool($json) ? $json : '';
	}

	/**
	 * Method to decode a json string to an object
	 *
	 * @param string $string
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	public function jsonToObject(string $string) : stdClass {
		if (!$this->isJson($string)) {
			//throw new \Exception('Given string is not a json object');
			return new stdClass();
		}

		return (object) json_decode( $string, true, 512, JSON_THROW_ON_ERROR );
	}

	/**
	 * Method to validate whether a string is a json object
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	function isJson(string $string) : bool {
		$data = json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	/**
	 * Method to translate bool and int
	 *
	 * @param bool $bool  variable to translate
	 * @param bool $toDb  whether direction is towards db or from db
	 *
	 * @return bool|int   int on going to db, bool on coming from db
	 */
	public function bool($bool, bool $toDb) {
		if ($toDb) {
			return $bool ? 1 : 0;
		} else {
			$bool = intval($bool);
			return $bool > 0;
		}
	}

	/**
	 * Method to translate datetime
	 *
	 * @param      $value
	 * @param bool $toDb
	 *
	 * @return string
	 */
	public function datetime($value, bool $toDb) {
		if ($value instanceof DateTime) {
			$value = date('Y-m-d H:i:s', strtotime($value->__toString()));
		}
		if ($value instanceof \DateTime) {
            $value = $value->format('Y-m-d H:i:s');
        }

		if ($toDb) {
			return (new \DateTime($value))->format('Y-m-d H:i:s');
		} else {
			return new \DateTime($value);
		}
	}

	/**
	 * Method to translate time
	 *
	 * @param      $value
	 * @param bool $toDb
	 *
	 * @return string
	 */
	public function time($value, bool $toDb): string {
		return date('H:i:s', strtotime($value));
	}

    /**
     * @param mixed $value
     * @param bool $toDb
     *
     * @return \DateTime|int
     */
    public function timestamp( mixed $value, bool $toDb ): \DateTime|int {
        if ($value instanceof DateTime) {
            $value = $value->__toString();
        }

        return $value instanceof \DateTime ? $value : new \DateTime($value);

	}
}