<?php declare(strict_types=1);

namespace HoneywellOld\Controller;

use Swift\Configuration\Configuration;
use Swift\Controller\Controller;
use Swift\Router\HTTPRequest;
use Swift\Router\Types\RouteTypesEnum;
use HoneywellOld\Helper\Authentication;

//use Swift\Annotations\Annotation\Route;
use Swift\Router\Attributes\Route;

class ControllerAuthorize extends Controller
{

	/**
	 * @var Authentication $authenticationHelper
	 */
	protected $authenticationHelper;

    /**
     * @var Configuration $configuration
     */
	private $configuration;

    /**
     * ControllerAuthorize constructor.
     *
     * @param HTTPRequest $HTTPRequest
     * @param Authentication $helperAuthentication
     * @param Configuration $configuration
     */
    #[Route(type: 'GET|POST', route: '/honeywell/authorization/', authRequired: false)]
	public function __construct(
			HTTPRequest $HTTPRequest,
			Authentication $helperAuthentication,
            Configuration $configuration) {
		parent::__construct($HTTPRequest);

		$this->authenticationHelper = $helperAuthentication;
		$this->configuration        = $configuration;
	}

	/**
	 * Method to get device from remote, and save/update it to the api
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	#[Route(type: 'GET', route: '/update/')]
	public function refreshAuthorization(array $params = array()) : void {
	    if (!$code = $this->HTTPRequest->request->input->get('code')) {
	        return;
        }

	    $this->configuration->set('honeywell.authorization_code', $this->HTTPRequest->request->input->get('code'), 'app/honeywell');

	    // TODO: Make authorize work
	}


}