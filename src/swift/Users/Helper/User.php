<?php declare(strict_types=1);

namespace Swift\Users\Helper;

use Swift\Authentication\Auth;
use Swift\Authentication\Model\Client;

class User {

	/**
	 * @var Client $modelClient
	 */
	private $modelClient;

	/**
	 * @var Auth $auth
	 */
	private $auth;

	/**
	 * User constructor.
	 *
	 * @param Client $modelClient
	 * @param Auth   $auth
	 */
	public function __construct(
		Client  $modelClient,
		Auth    $auth
	) {
		$this->modelClient  = $modelClient;
		$this->auth         = $auth;
	}


	/**
	 * Method to encrypt a password
	 *
	 * @param string $plainPassword
	 *
	 * @return string
	 */
	public function encryptPassword(string $plainPassword) : string {
		$options = array(
			'cost' => 12,
		);
		return password_hash($plainPassword, PASSWORD_BCRYPT, $options);
	}

	/**
	 * Method to verify whether a given password matches the hash
	 *
	 * @param string $plainPassword
	 * @param string $passwordHash
	 *
	 * @return bool
	 */
	public function passwordCorrect(string $plainPassword, string $passwordHash) : bool {
		return password_verify($plainPassword, $passwordHash);
	}

	/**
	 * Method to get current user
	 *
	 * @return int|null
	 */
	public function getCurrentUserID() : ?int {
		$token  = $this->auth->extractToken();
		if (is_null($token)) {
			return null;
		}

		$client = $this->modelClient->getTokenByValue($token);

		return is_null($client) || $client->id < 1 ? null : $client->userID;
	}
}