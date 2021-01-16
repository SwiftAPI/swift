<?php declare(strict_types=1);

namespace HoneywellOld\Controller;

use Swift\Controller\Controller;
use Swift\Http\Response\JSONResponse;
use Swift\Router\HTTPRequest;
use HoneywellOld\Helper\Authentication;
use HoneywellOld\Model\ModelLocations;
use Swift\Router\Attributes\Route;

class ControllerLocations extends Controller
{

	/**
	 * @var Authentication $helperAuthentication
	 */
	protected $helperAuthentication;

	/**
	 * @var ModelLocations $modelLocations
	 */
	protected $modelLocations;

	/**
	 * Locations constructor.
	 *
	 * @param HTTPRequest    $HTTPRequest
	 * @param Authentication $helperAuthentication
	 * @param ModelLocations $modelLocations
	 */
	#[Route(type: "GET|POST", route: "/honeywell/locations/")]
	public function __construct(
			HTTPRequest $HTTPRequest,
			Authentication $helperAuthentication,
			ModelLocations $modelLocations
	) {
		parent::__construct($HTTPRequest);

		$this->helperAuthentication = $helperAuthentication;
		$this->modelLocations       = $modelLocations;
	}

	#[Route(type: "GET", route: "/list/")]
	public function list() : JSONResponse {
		$this->helperAuthentication->refresh_token();
		$locations = $this->modelLocations->list();

		return new JSONResponse($locations);
	}
}