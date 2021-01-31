<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\AuthenticationDeprecated\Model;

use Exception;
use stdClass;
use Swift\AuthenticationDeprecated\Helper\Token as HelperToken;
use Swift\AuthenticationDeprecated\Model\Entity\Token;
use Swift\Kernel\Attributes\Autowire;
use Swift\Model\Entity\EntityManagerList;
use Swift\Model\Entity\EntityManagerSingle;
use Swift\AuthenticationDeprecated\Model\Entity\Client as entityClient;
use Swift\Model\HenriModelBase;

/**
 * Class Client
 * @package Swift\AuthenticationDeprecated\Model
 */
#[Autowire]
class Client extends HenriModelBase {

	/**
	 * @var HelperToken $helperToken
	 */
	private $helperToken;

	/**
	 * @var entityClient $entityClient
	 */
	private $entityClient;

	/**
	 * @var Token $entityToken
	 */
	private $entityToken;

	/**
	 * Client constructor.
	 *
	 * @param EntityManagerSingle $entityManagerSingle
	 * @param EntityManagerList   $entityManagerList
	 * @param HelperToken         $helperToken
	 * @param entityClient        $entityClient
	 * @param Token               $entityToken
	 */
	public function __construct(
		EntityManagerSingle $entityManagerSingle,
		EntityManagerList $entityManagerList,
		HelperToken $helperToken,
		EntityClient $entityClient,
		Token $entityToken
	) {
		$this->helperToken      = $helperToken;
		$this->entityClient     = $entityClient;
		$this->entityToken      = $entityToken;
		parent::__construct($entityManagerSingle, $entityManagerList);
	}

    /**
     * Method to retrieve client from database by apikey
     *
     * @param string $apikey
     * @param string $domain
     *
     * @return stdClass|null
     * @throws Exception
     */
	public function getClientByApiKeyAndDomain(string $apikey, string $domain): ?stdClass {
		$dataOjb            = $this->entityClient->getPropertiesAsObject();
		$dataOjb->apikey    = $apikey;
		if ($domain !== 'self') {
			$dataOjb->domain    = $domain;
		}

		return $this->entityClient->findOne($dataOjb);
	}

	/**
	 * Method to get client by a given key
	 *
	 * @param string $searchBy
	 * @param        $value
	 *
	 * @return stdClass|null
	 * @throws Exception
	 */
	public function getClient(string $searchBy, $value): ?stdClass {
		if (!$this->entityClient->hasField($searchBy)) {
			throw new Exception('Property ' . $searchBy . ' does not exist');
		}

		return $this->entityClient->findOne([$searchBy => $value]);
	}

	/**
	 * Method to get token by value
	 *
	 * @param string $tokenValue
	 *
	 * @return stdClass|null
	 */
	public function getTokenByValue(string $tokenValue): ?stdClass {
		return $this->entityToken->findOne(['value' => $tokenValue]);
	}

	/**
	 * Method to create a new client
	 *
	 * @param string      $domain
	 * @param string|null $apikey
	 * @param string|null $secret
	 *
	 * @return stdClass
     */
	public function createClient(string $domain, string $apikey = null, string $secret = null): stdClass {
		if (!$domain) {
			throw new Exception('No input given', 500);
		}

		if (!is_null($client = $this->getClient('domain', $domain))) {
			throw new Exception('Client ' . $domain . ' already exists with id ' . $client->id);
		}

		return $this->entityClient->save([
            'domain' => $domain,
            'apikey' => $apikey ?? $this->helperToken->generateUniqueToken($domain),
            'secret' => $secret ?? $this->helperToken->generateUniqueToken( time(), 15),
        ]);
	}

	/**
	 * Method to save/update token
	 *
	 * @param int    $clientID
	 * @param string $tokenValue
	 * @param string $level
	 * @param int    $userID
	 * @param int    $tokenID
	 *
     */
	public function saveToken(int $clientID, string $tokenValue, string $level = 'token', int $userID = 0, int $tokenID = 0) : stdClass {
	    $checkExistance = array(
	        'value' => $tokenValue,
        );
	    if ($tokenID !== 0) {
	        $checkExistance['id'] = $tokenID;
        }
		$token  = $this->entityToken->findOne($checkExistance);

		if (is_null($token)) {
		    $token = $this->entityClient->getPropertiesAsObject();
        }

		$token->clientID        = $clientID;
		$token->expirationDate  = date('Y-m-d H:i:s', strtotime('+ 1 day'));
		$token->userID          = $userID;
		$token->level           = $level;

		return $this->entityToken->save($token);
	}
}