<?php declare(strict_types=1);

namespace HoneywellOld\Controller;

use JetBrains\PhpStorm\Deprecated;
use Swift\Controller\Controller;
use Swift\Http\Response\JSONResponse;
use Swift\HttpFoundation\Request;
use Swift\Router\HTTPRequest;
use HoneywellOld\Helper\Authentication;
use HoneywellOld\Model\ModelThermostat;
use Swift\Router\Attributes\Route;

class ControllerThermostat extends Controller
{

	/**
	 * @var Authentication $authenticationHelper
	 */
	protected $authenticationHelper;

	/**
	 * @var ModelThermostat $modelThermostat
	 */
	protected $modelThermostat;

	/**
	 * HoneywellControllerThermostat constructor.
	 *
	 * @param HTTPRequest          $HTTPRequest
	 * @param Authentication       $helperAuthentication
	 * @param ModelThermostat      $honeywellModelThermostat
	 */
	#[Route(type: "GET|POST", route: "/honeywell/thermostat/", authRequired: false, authLevel: "login")]
	public function __construct(
        #[Deprecated( replacement: Request::class )] HTTPRequest $HTTPRequest,
        Authentication $helperAuthentication,
        ModelThermostat $honeywellModelThermostat) {
		parent::__construct($HTTPRequest);

		$this->authenticationHelper = $helperAuthentication;
		$this->modelThermostat      = $honeywellModelThermostat;
	}

	/**
	 * Method to set honeywell schedule
	 *
	 * @param array $params
	 *
	 * @return JSONResponse
	 * @throws \Exception
	 */
	#[Route(type: "POST", route: "/[i:device_id]/schedule/[a:action]/[i:schedule_id]?", authRequired: true, authLevel: "login")]
	public function setSchedule(array $params = array()) : JSONResponse {
		$response = new JSONResponse();
		$option = !empty($params['action']) ? $params['action'] : '' ;

		$data = array(
			'device_id'     => intval($params['device_id']),
			'schedule_id'   => !empty($params['schedule_id']) ? intval($params['schedule_id']) : 0,
		);
		$data = array_merge($data, $this->HTTPRequest->request->input->getArray());

		switch ($option ) {
			case 'new':
				$response->setResponse($this->modelThermostat->createNewSchedule($data));
				break;
			case 'untilnext':
				if ($this->modelThermostat->setUntilNextSchedule($data['device_id'], $data['setTemp'])) {
					$response->setResponse(array('state' => true,));
				} else {
					$response->setResponse(array('state' => false));
				}
				break;
			default:
				$response::notFound();
		}

		return $response;
	}

	/**
	 * Method to update a given schedule
	 *
	 * @param array $params
	 *
	 * @return JSONResponse
	 */
	#[Route(type: "PATCH", route: "/[i:device_id]/schedule/[i:schedule_id]", authRequired: true, authLevel: "login")]
	public function updateSchedule(array $params = array()) : JSONResponse {
		try {
			$updatedSchedule    = $this->modelThermostat->updateSchedule(intval($params['device_id']), intval($params['schedule_id']), $this->HTTPRequest->request->input->getArray());

			return new JSONResponse($updatedSchedule);
		} catch (\Exception $exception) {
			// TODO: Error
			$response = new JSONResponse();
			$response::notFound();
			return $response;
		}
	}

	/**
	 * Method to delete a given schedule
	 *
	 * @param array $params
	 *
	 * @return JSONResponse
	 */
	#[Route(type: "DELETE", route: "/[i:device_id]/schedule/[i:schedule_id]", authRequired: true, authLevel: "login")]
	public function deleteSchedule(array $params = array()) : JSONResponse {
		try {
			$this->modelThermostat->deleteSchedule(intval($params['device_id']), intval($params['schedule_id']));

			return new JSONResponse(array('state' => 'success'));
		} catch (\Exception $exception) {
			// TODO: Error
			$response = new JSONResponse();
			$response::notFound();
			return $response;
		}
	}

	/**
	 * Method to load and return honeywell schedule or device
	 *
	 * @param array $params
	 *
	 * @return JSONResponse
	 * @throws \Exception
	 */
	#[Route(type: "GET|POST", route: "/[i:device_id]/get/[a:action]/", authRequired: false, authLevel: "login")]
	public function get(array $params = array()) : JSONResponse {
		$option = !empty($params['action']) ? $params['action'] : '' ;
		if ($option) {
			switch ($option ) {
				case 'schedule':
					$result = $this->modelThermostat->getSchedule($this->HTTPRequest->request->input->get('from'), intval($this->HTTPRequest->request->input->get('to')));
					break;
				case 'device':
					$result = $this->modelThermostat->getDevice($params['device_id']);
					break;
			}
		}

		$response = new JSONResponse($result);

		return $response;
	}

	/**
	 * Method to get device from remote, and save/update it to the api
	 *
	 * @param array $params
	 *
	 * @return JSONResponse
	 * @throws \Dibi\Exception
	 */
	#[Route(type: "GET", route: "[a:device_id]/remote/get/[a:action]")]
	public function remoteGet(array $params = array()) : JSONResponse {
		// TODO: Logic to get thermostat from api
		$option = !empty($params['action']) ? $params['action'] : '' ;
		if ($option) {
			switch ($option ) {
				case 'device':
					$result = $this->modelThermostat->remoteUpdateThermostat($params['device_id']);
					$response = new JSONResponse(array('state' => 'success'));
					break;
				case 'schedule':
					$result = $this->modelThermostat->remoteGetSchedule($params['device_id']);
					$response = new JSONResponse($result);
					break;
				default:
					$response = new JSONResponse();
					$response::notFound();
					break;
			}
		}

		return $response;
	}

	/**
	 * Method to get device from remote, and save/update it to the api
	 *
	 * @param array $params
	 *
	 * @return JSONResponse
	 * @throws \Dibi\Exception
	 */
	#[Route(type: "GET", route: "[i:device_id]/remote/set/[a:action]")]
	public function remoteSet(array $params = array()) : JSONResponse {
		// TODO: Logic to get thermostat from api
		$option     = !empty($params['action']) ? $params['action'] : '' ;
		if ($option) {
			switch ($option ) {
				case 'device':
					//$result = $this->modelThermostat->remoteGetDevice($params['device_id']);
					//break;
				case 'schedule':
					$this->modelThermostat->remoteUpdateSchedule($params['device_id']);
					$response = new JSONResponse(array('state' => 'success', 'message' => 'Successfully updated'));
					break;
				default:
					$response = new JSONResponse();
					$response::notFound();
					break;
			}
		}

		return $response;
	}


}