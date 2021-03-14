<?php declare(strict_types=1);

namespace Swift\Users\Controller;

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Pure;
use Swift\Controller\AbstractController;
use Swift\Http\Response\JSONResponse;
use Swift\HttpFoundation\Request;
use Swift\Kernel\ServiceLocator;
use Swift\Router\HTTPRequest;
use Swift\Users\Model\User as ModelUser;
use Swift\AuthenticationDeprecated\Auth;
use Swift\Users\Helper\User as HelperUser;
use Swift\Router\Attributes\Route;

class ControllerUser extends AbstractController {
	/**
	 * @var ModelUser $modelUser
	 */
	private ModelUser $modelUser;

	/**
	 * @var Auth $auth
	 */
	private Auth $auth;

	/**
	 * @var HelperUser $helperUser
	 */
	private HelperUser $helperUser;

    /**
     * ControllerUser constructor.
     *
     * @param Request $HTTPRequest $HTTPRequest
     * @param ModelUser $modelUser
     * @param Auth $auth
     * @param HelperUser $helperUser
     * @param ServiceLocator $serviceLocator
     */
	#[Pure] #[Route(type: 'GET|POST|PUT', route: '/users/user/', authRequired: true)]
	public function __construct(
        Request $HTTPRequest,
        ModelUser $modelUser,
        Auth $auth,
        HelperUser $helperUser,
        ServiceLocator $serviceLocator,
	) {
		$this->modelUser    = $modelUser;
		$this->auth         = $auth;
		$this->helperUser   = $helperUser;
	}

	/**
	 * Method to verify user login
	 *
	 * @return JSONResponse
	 * @throws \Exception
	 */
//	#[Route(type: 'POST', route: '/login/')]
//	public function login(): JSONResponse {
//		$data       = $this->auth->decode('secret', $this->HTTPRequest->request->input->get('payload'));
//
//		$username   = !empty($data['username']) ? $data['username'] : false;
//		$password   = !empty($data['password']) ? $data['password'] : false;
//
//		if (!$username || !$password || !$this->modelUser->userMayLogin($username, $password)) {
//			// User not allowed to log in
//			$response   = new JSONResponse();
//			$response::notAuthorized();
//			return $response;
//		}
//
//		$user = $this->modelUser->getPopulatedUser();
//
//		// Update token
//		$token  = $this->auth->getToken();
//		$token->userID  = $user->id;
//		$token->level   = 'login';
//		$this->auth->updateToken($token);
//
//		$response   = new JSONResponse(array(
//			'state' => 'logged in',
//		));
//
//		return $response;
//	}

	/**
	 * @return JSONResponse
	 */
	#[Route(type: 'POST', route: '/create/')]
	public function create(): JSONResponse {
		$username   = $this->HTTPRequest->request->input->get('username');
		$password   = $this->HTTPRequest->request->input->get('password');
		$email      = $this->HTTPRequest->request->input->get('email') ? $this->HTTPRequest->request->input->get('email') : '';

		$this->modelUser->createUser($username, $password, $email);

		$response   = new JSONResponse(array('state' => 'created'));

		return $response;
	}

	/**
	 * Method to get the current logged in user
	 *
	 * @return JSONResponse
	 */
	#[Route(type: 'GET', route: '/me/')]
	public function getUserData(): JSONResponse {
		$userID = $this->helperUser->getCurrentUserID();

		if (is_null($userID)) {
			$response   = new JSONResponse();
			$response::notFound();
			return $response;
		}
		$user   = $this->modelUser->getUserByID($userID);

		if (is_null($user)) {
			$response   = new JSONResponse();
			$response::notFound();
			return $response;
		}

		return new JSONResponse($user);
	}


}