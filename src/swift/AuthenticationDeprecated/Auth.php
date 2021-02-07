<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\AuthenticationDeprecated;

use Swift\AuthenticationDeprecated\Model\Client;
use Swift\Kernel\Attributes\Autowire;
use Swift\Router\Exceptions\NotAuthorizedException;
use Swift\Router\HTTPRequest;

/**
 * Class Auth
 * @package Swift\AuthenticationDeprecated
 */
#[Autowire]
class Auth {

	/**
	 * @var JwtEncoder $jwtEncoder
	 */
	private JwtEncoder $jwtEncoder;

	/**
	 * @var HTTPRequest $httpRequest
	 */
	private HTTPRequest $httpRequest;

	/**
	 * @var Client $modelClient
	 */
	private Client $modelClient;

	private const HEADER_VALUE_PATTERN = "/Bearer\s+(.*)$/i";

	/**
	 * Auth constructor.
	 *
	 * @param JwtEncoder    $jwtEncoder
	 * @param Client        $modelClient
	 * @param HTTPRequest   $HTTPRequest
	 */
	public function __construct(
		JwtEncoder      $jwtEncoder,
		Client          $modelClient,
		HTTPRequest     $HTTPRequest
	) {
		$this->jwtEncoder       = $jwtEncoder;
		$this->modelClient      = $modelClient;
		$this->httpRequest      = $HTTPRequest;
	}

    /**
     * Method to validate request
     *
     * @param string $key
     * @param string $authLevels
     *
     * @return void
     * @throws \Dibi\Exception
     * @throws NotAuthorizedException
     */
	public function validate(string $key, array $authLevels): void {
	    $authLevel = $authLevels[0];
		$this->jwtEncoder->setKey($key);
		$jwt = $this->extractToken();

		if (empty($jwt)) {
			throw new NotAuthorizedException('Not token could be extracted');
		}

		$token = $this->modelClient->getTokenByValue($jwt);

		if (is_null($token)) {
			throw new NotAuthorizedException('Token not found');
		}

		if (strtotime($token->expirationDate) < strtotime('now')) {
			throw new NotAuthorizedException('Token has expired');
		}

		if ($this->authLevelToInt($token->level) < $this->authLevelToInt($authLevel)) {
			// Token has not the correct rights
			throw new NotAuthorizedException('Not access to the given resource level', 403);
		}

		// Extend expiration date by 1 day
		$token->expirationDate  = date('Y-m-d H:i:s', strtotime('+ 1 day'));
		$this->updateToken($token);

		$payload = $this->jwtEncoder->decode($jwt);

		if (is_null($payload)) {
		    throw new NotAuthorizedException('Authorization not passed');
        }
	}

	public function decode(string $secret, string $payload) {
		return $this->jwtEncoder->decode($payload, $secret);
	}

	/**
	 * Method to generate a token
	 *
	 * @param string $secret
	 * @param        $payload
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function generateToken(string $secret, $payload) : string {
		$payload    = (object) $payload;
		return $this->jwtEncoder->encode($secret, $payload);
	}

	/**
	 * Method to get token by value
	 *
	 * @param string $tokenValue
	 *
	 * @return \stdClass|null
	 */
	public function getToken(string $tokenValue = '') : ?\stdClass {
		if (!$tokenValue) {
			$tokenValue = $this->extractToken();
		}

		return $this->modelClient->getTokenByValue($tokenValue);
	}

	/**
	 * Method to update token
	 *
	 * @param \stdClass $token
	 *
	 * @return \stdClass    updated token
	 * @throws \Dibi\Exception
	 */
	public function updateToken(\stdClass $token) : \stdClass {
		return $this->modelClient->saveToken($token->clientID, $token->value, $token->level, $token->userID, $token->id);
	}

	/**
	 * Method to extract token
	 *
	 * @return string|null  token string on found, null on token not set
	 */
	public function extractToken(): ?string {
		$authHeader = $this->httpRequest->request->getHeader('Authorization');

		if (empty($authHeader)) {
			return null;
		}

		if (preg_match(self::HEADER_VALUE_PATTERN, $authHeader, $matches)) {
			return $matches[1];
		}

		return null;
	}

	/**
	 * Method to translate level to int for easy rights comparison
	 *
	 * @param string $authLevel
	 *
	 * @return int
	 */
	private function authLevelToInt(string $authLevel) : int {
		$level = 0;
		switch ($authLevel) {
			case 'apikey':
				$level  = 3;
				break;
			case 'token':
				$level  = 6;
				break;
			case 'login':
				$level  = 9;
				break;
		}

		return $level;
	}
}