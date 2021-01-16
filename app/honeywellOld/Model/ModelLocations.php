<?php declare(strict_types=1);

namespace HoneywellOld\Model;


use Swift\Http\Request\Request;
use Swift\Model\Entity\EntityManagerList;
use Swift\Model\Entity\EntityManagerSingle;
use HoneywellOld\Helper\Authentication;
use HoneywellOld\Helper\DataFormats;
use HoneywellOld\Model\Base\BaseModel;

class ModelLocations extends BaseModel
{

	/**
	 * @var Request $helperRequest
	 */
	private $helperRequest;

	/**
	 * @var DataFormats $helperDataFormats
	 */
	private $helperDataFormats;

	/**
	 * ModelLocations constructor.
	 *
	 * @param EntityManagerSingle $entityManagerSingle
	 * @param EntityManagerList   $entityManagerList
	 * @param Authentication      $helperAuthentication
	 * @param Request             $helperRequest
	 * @param DataFormats         $helperDataFormats
	 */
	public function __construct(
			EntityManagerSingle $entityManagerSingle,
			EntityManagerList $entityManagerList,
			Authentication $helperAuthentication,
			Request $helperRequest,
			DataFormats $helperDataFormats
	) {
		parent::__construct($entityManagerSingle, $entityManagerList, $helperAuthentication);

		$this->helperRequest      = $helperRequest;
		$this->helperDataFormats  = $helperDataFormats;
	}

	/**
	 * Method to get list of location from Honeywell API
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function list() : array {
		$apikey = $this->helperAuthentication->get('apikey');
		$access_token = $this->helperAuthentication->get('access_token');
		$url = 'https://api.honeywell.com/v2/locations';

		$headers = array(
				'Authorization'  => 'Bearer ' . $access_token,
		);
		$query = array(
				'apikey'      => $apikey,
		);

		if (_HDEV || _HDEBUG) {
			$this->helperRequest::verifyPeer(false);
			$this->helperRequest::verifyHost(false);
		}

		$response = $this->helperRequest::get($url, $headers, $query);

		if ($response->code !== 200) {
			$error = _HDEV || _HDEBUG ? $response->body->message : 'Remote service unavailable';
			throw new \Exception($error, 500);
		}

		return $response->body;
	}

	public function remoteGetLocation(int $locationID) : \stdClass {
		$apikey = $this->helperAuthentication->get('apikey');
		$access_token = $this->helperAuthentication->get('access_token');
		$url = 'https://api.honeywell.com/v2/locations';

		$headers = array(
				'Authorization'  => 'Bearer ' . $access_token,
		);
		$query = array(
				'apikey'      => $apikey,
		);

		if (_HDEV || _HDEBUG) {
			$this->helperRequest::verifyPeer(false);
			$this->helperRequest::verifyHost(false);
		}

		$response = $this->helperRequest::get($url, $headers, $query);

		if ($response->code !== 200) {
			$error = _HDEV || _HDEBUG ? $response->body->message : 'Remote service unavailable';
			throw new \Exception($error, 500);
		}

		$location = new \stdClass();
		if (!is_array($response->body) || empty($response->body)) {
			return $location;
		}

		foreach ($response->body as $item) {
			if ($item->locationID !== $locationID) {
				continue;
			}

			$location = $this->helperDataFormats->honeywellLocationToLocation($item);
		}

		return $location;
	}
}