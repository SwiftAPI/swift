<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\AuthenticationDeprecated;

use Firebase\JWT\JWT;

/**
 * Class JwtEncoder
 * @package Swift\AuthenticationDeprecated
 */
class JwtEncoder {

	/**
	 * @var string  $key
	 */
	private $key = '';

	/**
	 * Method to set key value
	 *
	 * @param string $key
	 */
	public function setKey(string $key) : void {
		$this->key = $key;
	}

	/**
	 * Method to decode a key
	 *
	 * @param string $jwt
	 *
	 * @return array
	 */
	public function decode(string $jwt, string $key = '') : array {
		$key        = !$key ? $this->key : $key;
		if (!$key) {
			throw new \Exception('No encoding key specified', 500);
		}
		$decoded = JWT::decode($jwt, $key, ['HS256']);
		return (array)$decoded;
	}

    /**
     * Method to encode data
     *
     * @param string $key
     * @param \stdClass|null $payload
     *
     * @return string
     * @throws \Exception
     */
	public function encode(string $key = '', ?\stdClass $payload = null) : string {
		$key        = !$key ? $this->key : $key;
		if (!$key) {
			throw new \Exception('No encoding key specified', 500);
		}

		return JWT::encode($payload, $key);
	}
}