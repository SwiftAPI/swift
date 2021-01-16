<?php declare(strict_types=1);

namespace HoneywellOld\Model;

use Dibi\Exception;
use Swift\Http\Request\Request;
use Swift\Model\Entity\EntityManagerSingle;
use Swift\Model\Entity\EntityManagerList;
use HoneywellOld\Helper\Authentication;
use HoneywellOld\Helper\DataFormats;
use HoneywellOld\Helper\Schedule as HelperSchedule;
use HoneywellOld\Model\Entity\Thermostat\LogThermostat;
use HoneywellOld\Model\Entity\Thermostat\Thermostat;
use HoneywellOld\Model\Entity\Thermostat\Schedule;
use Unirest\Request\Body;

class ModelThermostat extends Base\BaseModel {

	/**
	 * @var ModelLocations $modelLocations
	 */
	private $modelLocations;

	/**
	 * @var Thermostat $entityThermostat
	 */
	private $entityThermostat;

	/**
	 * @var Schedule $entitySchedule
	 */
	private $entitySchedule;

	/**
	 * @var LogThermostat $entityLogThermostat
	 */
	private $entityLogThermostat;

	/**
	 * @var Request $helperRequest
	 */
	private $helperRequest;

	/**
	 * @var DataFormats $helperDataFormats
	 */
	private $helperDataFormats;

	/**
	 * @var HelperSchedule $helperSchedule
	 */
	private $helperSchedule;

	/**
	 * ModelThermostat constructor.
	 *
	 * @param ModelLocations      $modelLocations
	 * @param EntityManagerSingle $entityManagerSingle
	 * @param EntityManagerList   $entityManagerList
	 * @param Authentication      $helperAuthentication
	 * @param Thermostat          $entityThermostat
	 * @param Schedule            $entitySchedule
	 * @param LogThermostat       $entityLogThermostat
	 * @param Request             $helperRequest
	 * @param DataFormats         $helperDataFormats
	 * @param HelperSchedule      $helperSchedule
	 */
	public function __construct(
		ModelLocations  $modelLocations,
		EntityManagerSingle $entityManagerSingle,
		EntityManagerList $entityManagerList,
		Authentication $helperAuthentication,
		Thermostat $entityThermostat,
		Schedule $entitySchedule,
		LogThermostat $entityLogThermostat,
		Request $helperRequest,
		DataFormats $helperDataFormats,
		HelperSchedule $helperSchedule) {
		parent::__construct($entityManagerSingle, $entityManagerList, $helperAuthentication);

		$this->modelLocations         = $modelLocations;
		$this->entityThermostat       = $entityThermostat;
		$this->entitySchedule         = $entitySchedule;
		$this->entityLogThermostat    = $entityLogThermostat;
		$this->helperRequest          = $helperRequest;
		$this->helperDataFormats      = $helperDataFormats;
		$this->helperSchedule         = $helperSchedule;
	}

	/**
	 * Method to create new schedule for a given device
	 *
	 * @param array $settings
	 *
	 * @return \stdClass
	 */
	public function createNewSchedule(array $settings) : \stdClass {
		$schedule = $this->entitySchedule->getValuesAsObject();

		$schedule->title        = $settings['title'];
		$schedule->deviceID     = intval($settings['device_id']);
		$schedule->temp         = $settings['temp'];
		$schedule->start        = date('Y-m-d H:i:s', strtotime('now'));
		$schedule->end          = date('Y-m-d H:i:s', strtotime('+ 1 year'));
		$schedule->type         = $settings['mode'];
		$schedule->geofenced    = $settings['geoLocate']['enableGeo'];
		$schedule->geoAwayTemp  = $settings['geoLocate']['geoAwayTemp'];
		$schedule->geoRadius    = $settings['geoLocate']['geoRadius'];
		$schedule->params       = $this->helperSchedule->populateParams($settings);
		$schedule->state        = 1;
		$schedule->created      = date('Y-m-d', strtotime('now'));

		// TODO: Validation feedback

		$this->entitySchedule->populateState($schedule, false);
		$this->entitySchedule->save();

		$values = $this->entitySchedule->getValuesAsObject();

		$this->entitySchedule->reset();
		$this->remoteUpdateSchedule($schedule->deviceID);

		return $values;
	}

	/**
	 * Method to create or update until next schedule item
	 *
	 * @param int   $deviceID
	 * @param float $temp
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function setUntilNextSchedule(int $deviceID, float $temp) : bool {
		$currentSchedule    = $this->getCurrentRunningSchedule($deviceID);

		$schedule = $this->entitySchedule->getValuesAsObject();

		if ($currentSchedule->type === 'till_next') {
			$schedule->id   = $currentSchedule->id;
			$this->entitySchedule->populateState($schedule, true);
			$schedule       = $this->entitySchedule->getValuesAsObject();
		} else {
			$schedule->title        = 'Until next';
			$schedule->deviceID     = $deviceID;
			$schedule->start        = date('Y-m-d H:i:s', strtotime('now'));
			$schedule->end          = date('Y-m-d H:i:s', strtotime('now'));
			$schedule->geofenced    = false;
			$schedule->geoAwayTemp  = 16;
			$schedule->geoRadius    = 3000;
			$schedule->type         = 'till_next';
			$schedule->params       = $this->helperSchedule->populateParams(array(
				'mode'      => $schedule->type,
				'timing'    => array(
					'startTime' => date('H:i', strtotime('now')),
					'endTime'   => date('H:i', strtotime($currentSchedule->params->endTime)),
				),
			));
			$schedule->state        = 1;
			$schedule->created      = date('Y-m-d', strtotime('now'));
		}
		$schedule->temp = $temp;
		$this->entitySchedule->populateState($schedule, false);
		$this->entitySchedule->save();

		// Submit changes to Honeywell API to prevent cron delays
		try {
			$this->entitySchedule->reset();
			$this->remoteUpdateSchedule($deviceID);
		} catch (\Exception $exception) {
			//var_dump($exception->getMessage() . ' ' . $exception->getFile() . ':' . $exception->getLine());
			//die();
		}

		return true;
	}

	/**
	 * Method to update a given schedule
	 *
	 * @param int   $deviceID
	 * @param int   $scheduleID
	 * @param array $settings
	 *
	 * @return \stdClass|null
	 * @throws Exception
	 */
	public function updateSchedule(int $deviceID, int $scheduleID, array $settings = array()) : ?\stdClass {
		$currentSchedule        = $this->entitySchedule->getValuesAsObject();
		$currentSchedule->id    = $scheduleID;
		$this->entitySchedule->populateState($currentSchedule, true);
		$currentSchedule        = $this->entitySchedule->getValuesAsObject();
		if (is_null($currentSchedule->title)) {
			throw new \Exception('Schedule not found', 404);
		}

		$currentSchedule->title         = $settings['title'];
		$currentSchedule->temp          = $settings['temp'];
		$currentSchedule->geofenced     = $settings['geoLocate']['enableGeo'];
		$currentSchedule->geoAwayTemp   = $settings['geoLocate']['geoAwayTemp'];
		//$currentSchedule->geoRadius     = $settings['geoLocate']['geoRadius'];
		$this->entitySchedule->populateState($currentSchedule, false);
		$this->entitySchedule->save();

		return $this->entitySchedule->getValuesAsObject();
	}

	/**
	 * Method to delete a given schedule
	 *
	 * @param int   $deviceID
	 * @param int   $scheduleID
	 *
	 * @throws Exception
	 */
	public function deleteSchedule(int $deviceID, int $scheduleID) : void {
		$currentSchedule        = $this->entitySchedule->getValuesAsObject();
		$currentSchedule->id    = $scheduleID;
		$this->entitySchedule->populateState($currentSchedule, true);
		$currentSchedule        = $this->entitySchedule->getValuesAsObject();
		if (is_null($currentSchedule->title)) {
			throw new \Exception('Schedule not found', 404);
		}

		$this->entitySchedule->delete();
	}

	/**
	 * Method to get device schedule
	 *
	 * @param string $from  from date
	 * @param string $to    to date
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getSchedule(string $from = '', string $to = '') : array {
		$from = $from ? date('Y-m-d', strtotime($from)) : date('Y-m-d', strtotime('now'));
		$to   = $to ? date('Y-m-d', strtotime($to)) : date('Y-m-d', strtotime('+ 2 weeks'));

		$fromDate = new \DateTime($from, new \DateTimeZone('Europe/Amsterdam'));
		$toDate   = new \DateTime($to, new \DateTimeZone('Europe/Amsterdam'));
		$interval = \DateInterval::createFromDateString('1 day');
		$period   = new \DatePeriod($fromDate, $interval, $toDate);

		$list = array();

		foreach ($period as $day) {
			$date = $day->format('d-m-Y');
			$nextDate   = date('d-m-Y', strtotime('+ 1 day', strtotime($day->format('d-m-Y'))));

			// Get the default schedules
			$prePopulateData        = $this->entitySchedule->getValuesAsObject();
			$prePopulateData->type  = 'default';

			$this->entityManagerList->setMainEntity($this->entitySchedule, 'schedule');
			$this->entityManagerList->populateState($prePopulateData, 'schedule', false);

			$defaultSchedules =  $this->entityManagerList->getList(array(
				'schedule'  => array('type', 'start', 'end'),
			), true);

			// Get the recurring schedules
			$prePopulateData->type = 'recurring';
			$this->entityManagerList->populateState($prePopulateData, 'schedule', false);

			$recurringSchedules = $this->entityManagerList->getList(array(
				'schedule'  => array('type', 'start', 'end'),
			), true);

			// Get the once occurring schedules
			$prePopulateData->type = 'once';
			$this->entityManagerList->populateState($prePopulateData, 'schedule', false);

			$onceSchedules = $this->entityManagerList->getList(array(
				'schedule'  => array('type', 'start', 'end'),
			), true);

			// Get the until next occurring schedules
			$prePopulateData->type  = 'till_next';
			$this->entityManagerList->populateState($prePopulateData, 'schedule', false);

			$untilNextSchedules = $this->entityManagerList->getList(array(
				'schedule'  => array('type', 'start', 'end'),
			), true);

			$schedule = $this->helperSchedule->getList($defaultSchedules, $recurringSchedules, $onceSchedules, $untilNextSchedules, $date, $nextDate)[$day->format('Y-m-d')];
			$list[$day->format('Y-m-d')]    = $schedule;
			$this->entityManagerList->reset();
			$this->entitySchedule->reset();
		}

		return $list;
	}

	/**
	 * Method to update schedule at Honeywell API
	 *
	 * @param int $deviceID
	 *
	 * @throws \Unirest\Exception
	 */
	public function remoteUpdateSchedule(int $deviceID) : void {
		$device             = $this->getDevice($deviceID);
		$currentSchedule    = $this->getCurrentRunningSchedule($deviceID);

		$scheduleObj    = new \stdClass();
		$scheduleObj->deviceID  = $device->deviceID;
		$scheduleObj->scheduleType      = 'Geofence';
		$scheduleObj->geoFenceSchedule  = new \stdClass();
		$scheduleObj->geoFenceSchedule->homePeriod  = new \stdClass();
		$scheduleObj->geoFenceSchedule->homePeriod->heatSetPoint    = $this->helperDataFormats->celsiusToFahrenheit($currentSchedule->temp);
		$scheduleObj->geoFenceSchedule->homePeriod->coolSetPoint    = 50;
		// Make temps the same if not geoFenced
		$scheduleObj->geoFenceSchedule->awayPeriod  = new \stdClass();
		$scheduleObj->geoFenceSchedule->awayPeriod->heatSetPoint    = $currentSchedule->geofenced ? $this->helperDataFormats->celsiusToFahrenheit($currentSchedule->geoAwayTemp) : $this->helperDataFormats->celsiusToFahrenheit($currentSchedule->temp);
		$scheduleObj->geoFenceSchedule->awayPeriod->coolSetPoint    = 50;

		if (!$this->helperAuthentication->authenticated) {
			try {
				$this->helperAuthentication->refresh_token();
			} catch (\Exception $exception) {
				throw new \Exception('Remote service unavailable', 500);
			}
		}

		// Get necessary variables
		$apikey         = $this->helperAuthentication->get('apikey');
		$access_token   = $this->helperAuthentication->get('access_token');
		$url = 'https://api.honeywell.com/v2/devices/schedule/' . $device->deviceID . '?apikey=' . $apikey . '&locationId=' . $device->locationID . '&type=regular';

		$headers = array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $access_token,
		);

		if (_HDEV || _HDEBUG) {
			$this->helperRequest::verifyPeer(false);
			$this->helperRequest::verifyHost(false);
		}

		$response = $this->helperRequest::post($url, $headers, Body::Json($scheduleObj));

		if ($response->code !== 200) {
			$error = _HDEV || _HDEBUG ? $response->body->message : 'Remote service unavailable';
			throw new \Exception($error, 500);
		}
	}

	/**
	 * Method to get schedule from remote
	 *
	 * @param string $deviceID
	 *
	 * @return \stdClass
	 * @throws \Exception
	 */
	public function remoteGetSchedule(int $deviceID) : \stdClass {
		// Get device data
		$this->entityThermostat->populateState(array('id' => $deviceID), true);
		$device = $this->entityThermostat->getValuesAsObject();

		if (!$this->helperAuthentication->authenticated) {
			try {
				$this->helperAuthentication->refresh_token();
			} catch (\Exception $exception) {
				throw new \Exception('Remote service unavailable', 500);
			}
		}

		// Get necessary variables
		$apikey = $this->helperAuthentication->get('apikey');
		$access_token = $this->helperAuthentication->get('access_token');
		$url = 'https://api.honeywell.com/v2/devices/schedule/' . $device->deviceID;

		$headers = array(
			'Authorization'  => 'Bearer ' . $access_token,
		);
		$query = array(
			'apikey'      => $apikey,
			'locationId'  => $device->locationID,
			'type'        =>  'regular',
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

		$response = $response->body;

		return $response;
	}

	/**
	 * Method to get device from remote
	 *
	 * @param int $deviceID
	 *
	 * @return \stdClass
	 * @throws \Exception
	 */
	public function remoteGetDevice(int $deviceID) : \stdClass {
		// Get device data
		$this->entityThermostat->populateState(array('id' => $deviceID), true);
		$device = $this->entityThermostat->getValuesAsObject();

		if (!$this->helperAuthentication->authenticated) {
			try {
				$this->helperAuthentication->refresh_token();
			} catch (\Exception $exception) {
				throw new \Exception('Remote service unavailable', 500);
			}
		}

		// Get necessary variables
		$apikey = $this->helperAuthentication->get('apikey');
		$access_token = $this->helperAuthentication->get('access_token');
		$url = 'https://api.honeywell.com/v2/devices';

		$headers = array(
			'Authorization'  => 'Bearer ' . $access_token,
		);
		$query = array(
			'apikey'      => $apikey,
			'locationId'  => $device->locationID,
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

		$response = $response->body;

		return $response[0];
	}

	/**
	 * Method to update thermostat from remote
	 *
	 * @param int $deviceID
	 *
	 * @return bool
	 * @throws \Dibi\Exception
	 */
	public function remoteUpdateThermostat(int $deviceID) : bool {
		$device   = $this->getDevice($deviceID);
		$hDevice  = $this->remoteGetDevice($deviceID);
		$location = $this->modelLocations->remoteGetLocation($device->locationID);

		if (!is_object($device)) {
			// Unexpected behaviour
			return false;
		}

		$hDevice = $this->helperDataFormats->HoneywellDeviceToDevice($hDevice);

		if ($hDevice->deviceID !== $device->deviceID) {
			return false;
		}

		$hDevice->state->occupated = $location->geofence->occupated;
		$hDevice->locationID       = $device->locationID;

		$preloadData = new \stdClass();
		$preloadData->deviceID = $hDevice->deviceID;
		$this->entityThermostat->populateState($preloadData, true); // Make sure to preload entity if possible

		// Now that the entity is preloaded from the db update the thermostat with the new data
		$this->entityThermostat->populateState($hDevice, false);
		$this->entityThermostat->save();

		$this->logThermostat($hDevice, $device->id);

		return true;
	}

	/**
	 * Method to log thermostat
	 *
	 * @param \stdClass $data
	 * @param int       $internalDeviceID
	 *
	 * @throws Exception
	 */
	private function logThermostat(\stdClass $data, int $internalDeviceID) : void {
		$logData    = new \stdClass();
		$logData->deviceID      = $internalDeviceID;
		$logData->heating       = $data->state->heating;
		$logData->setTemp       = $data->state->setTemp;
		$logData->indoorTemp    = $data->state->indoorTemp;
		$logData->occupated     = $data->state->occupated;
		$logData->date          = date('Y-m-d H:i:s', strtotime('now'));
		$logData->time          = date('H:i:s', strtotime('now'));

		$this->entityLogThermostat->populateState($logData, false);
		$this->entityLogThermostat->save();

		if (!$this->entityLogThermostat->get($this->entityLogThermostat->get('primaryKey'))) {
			throw new \Exception('Error on logging thermostat', 500);
		}
	}

	/**
	 * Method to get device by id
	 *
	 * @param int $deviceID
	 *
	 * @return \stdClass
	 */
	public function getDevice(int $deviceID) : \stdClass {
		$state = array('id' => $deviceID);
		$this->entityThermostat->populateState($state, true);

		$device = $this->entityThermostat->getValuesAsObject();

		try {
			$device->currentSchedule = $this->getCurrentRunningSchedule($deviceID);
		} catch (\Exception $exception) {
			//var_dump($exception->getMessage() . ' ' . $exception->getFile(). ':' . $exception->getLine());
		}

		return $device;
	}

	/**
	 * Method to get current running schedule item
	 *
	 * @param int $deviceID
	 *
	 * @return \stdClass
	 * @throws \Exception
	 */
	private function getCurrentRunningSchedule(int $deviceID) : \stdClass {
		$scheduleList   = $this->getSchedule('', date('d-m-Y', strtotime('tomorrow')))[date('Y-m-d', strtotime('now'))];
		$nowInTime      = strtotime('now');

		$match      = '';
		foreach ($scheduleList as $timeKey => $scheduleItem) {
			$scheduleItemStartTime  = strtotime($scheduleItem->params->startTime);
			$scheduleItemEndTime    = strtotime($scheduleItem->params->endTime);
			if ($nowInTime >= $scheduleItemStartTime && $nowInTime < $scheduleItemEndTime) {
				$match  = $timeKey;
			}
		}

		return $scheduleList[$match];
	}
}