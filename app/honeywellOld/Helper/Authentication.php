<?php declare(strict_types=1);

namespace HoneywellOld\Helper;

use Swift\Configuration\Configuration;
use Swift\Http\Request\Request;
use Unirest\Request\Body;

class Authentication
{

	/**
	 * @var Request $requestHelper
	 */
	private $requestHelper;

	/**
	 * @var Configuration $configuration
	 */
	private $configuration;

	/**
	 * @var string $apikey
	 */
	protected $apikey;

	/**
	 * @var string $apisecret
	 */
	protected $apisecret;

	/**
	 * @var string  $access_token
	 */
	protected $access_token;

	/**
	 * @var string $refresh_token
	 */
	protected $refresh_token;

	/**
	 * @var string  $expires_in
	 */
	protected $expires_in;

	/**
	 * @var string  $token_type
	 */
	protected $token_type;

	/**
	 * @var bool $authenticated
	 */
	public $authenticated = false;

	/**
	 * Authentication constructor.
	 *
	 * @param Request       $requestHelper
	 * @param Configuration $configuration
	 */
	public function __construct(
		Request $requestHelper,
		Configuration $configuration
	) {
		$this->requestHelper    = $requestHelper;
		$this->configuration    = $configuration;
		$this->apikey           = $this->configuration->get('honeywell.apikey', 'app/honeywell');
		$this->apisecret        = $this->configuration->get('honeywell.apisecret', 'app/honeywell');
		$this->refresh_token    = $this->configuration->get('honeywell.refresh_token', 'app/honeywell');
	}

	/**
	 * Method to authenticate with Honeywell
	 *
	 * @throws \Exception
	 */
	public function authenticate() : void {
//		$authorize = base64_encode($this->apikey . ':' . $this->apisecret);
//		$url = 'https://api.honeywell.com/oauth2/token';
//
//        $headers = array(
//            'Authorization' => 'Basic ' . $authorize,
//            'Content-Type' => 'application/x-www-form-urlencoded',
//        );
//		$query = array(
//			'grant_type'    =>  'authorization_code',
//			'code'          =>  $this->configuration->get('honeywell.authorization_code', 'app/honeywell'),
//			'redirect_uri'  =>  $this->configuration->get('honeywell.redirect_uri', 'app/honeywell')
//		);
//
//		if (_HDEV || _HDEBUG) {
//			$this->requestHelper::verifyPeer(false);
//			$this->requestHelper::verifyHost(false);
//		}
//
//		$response = $this->requestHelper::post($url, $headers, Body::Form($query));
//
//		if ($response->code !== 200) {
//			throw new \Exception($response->body->message, $response->code);
//		}
//
//		$this->authenticated  = true;
	}

	/**
	 * Method to refresh the token
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function refresh_token() : void {
		$authorize = base64_encode($this->apikey . ':' . $this->apisecret);
		$url = 'https://api.honeywell.com/oauth2/token';

		$headers = array(
			'Authorization' => $authorize,
		);
		$query = array(
			'grant_type'    => 'refresh_token',
			'refresh_token' => $this->refresh_token,
		);

		if (_HDEV || _HDEBUG) {
			$this->requestHelper::verifyPeer(false);
			$this->requestHelper::verifyHost(false);
		}

		$response = $this->requestHelper::post($url, $headers, Body::Form($query));

		if ($response->code !== 200) {
			throw new \Exception($response->body->fault->faultstring, $response->code);
		}

		$this->authenticated  = true;
		$this->access_token   = $response->body->access_token;
		$this->expires_in     = $response->body->expires_in;
		$this->token_type     = $response->body->token_type;

        $this->configuration->set('honeywell.refresh_token', $response->body->refresh_token, 'app/honeywell');
        $this->configuration->set('honeywell.access_token', $response->body->access_token, 'app/honeywell');
	}

	/**
	 * Method to get object property by name
	 *
	 * @param string $property
	 *
	 * @return mixed  property value on exists. false on non existent property
	 */
	public function get(string $property) {
		if (property_exists($this, $property)) {
			return $this->{$property};
		} else {
			return false;
		}
	}
}