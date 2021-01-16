<?php
/**
 * Created by Buro26.
 * Author: Henri
 * Date: 5-5-2020 18:24
 */

namespace Swift\Authentication;

use Firebase\JWT\JWT;

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