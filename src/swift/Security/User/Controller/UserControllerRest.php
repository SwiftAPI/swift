<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\Controller;

use Swift\Controller\AbstractController;
use Swift\HttpFoundation\Exception\BadRequestException;
use Swift\HttpFoundation\JsonResponse;
use Swift\Kernel\Attributes\Autowire;
use Swift\Router\Attributes\Route;
use Swift\Router\RouteParameter;
use Swift\Router\Types\RouteMethodEnum;
use Swift\Security\Authorization\AuthorizationTypesEnum;
use Swift\Security\User\Exception\UserAlreadyExistsException;
use Swift\Security\User\UserProviderInterface;

/**
 * Class UserControllerRest
 * @package Swift\Security\User\Controller
 */
#[Route(method: [RouteMethodEnum::GET, RouteMethodEnum::POST], route: '/users/', name: 'security.user'), Autowire]
class UserControllerRest extends AbstractController {

    /**
     * UserController constructor.
     *
     * @param UserProviderInterface $userProvider
     */
    public function __construct(
        private UserProviderInterface $userProvider,
    ) {
    }

    /**
     * Create user account endpoint
     *
     * @param RouteParameter[] $params
     *
     * @return JsonResponse
     */
    #[Route( method: RouteMethodEnum::POST, route: '/create/', name: 'security.user.create' )]
    public function create( array $params ): JsonResponse {
        $request = $this->getRequest()->getContent();

        $username = $request->get('username');
        $password = $request->get('password');
        $email = $request->get('email');
        $firstname = $request->get('firstname');
        $lastname = $request->get('lastname');

        if (!$username || !$password || !$email || !$firstname || !$lastname) {
            throw new BadRequestException('Invalid user input');
        }

        try {
            $data = $this->userProvider->storeUser($username, $password, $email, $firstname, $lastname)->serialize();
            unset($data->password);
        } catch (UserAlreadyExistsException $exception) {
            throw new BadRequestException(sprintf('User already exists: %s', $exception->getMessage()));
        }

        return new JsonResponse($data);
    }

    /**
     * Return currently authenticated user. For this it is required that a user is authenticated
     *
     * @param RouteParameter[] $params
     *
     * @return JsonResponse
     */
    #[Route( method: RouteMethodEnum::GET, route: '/me/', name: 'security.users.me', isGranted: [AuthorizationTypesEnum::IS_AUTHENTICATED] )]
    public function me( array $params ): JsonResponse {
        $data = $this->getCurrentUser()->serialize();
        unset($data->password);

        return new JsonResponse($data);
    }

    /**
     * Rest user authentication endpoint
     *
     * Authentication already occurs on the security component. So all that needs to be done is return the currently authenticated user
     *
     * Only a direct login is valid here. Re-authentication or no authentication is not valid. This is already cover through isGranted in the route (validated by the firewall)
     *
     * @param RouteParameter[] $params
     *
     * @return JsonResponse
     */
    #[Route( method: [RouteMethodEnum::POST], route: '/login/', name: 'security.user.login', isGranted: [AuthorizationTypesEnum::IS_AUTHENTICATED_DIRECTLY], tags: [Route::TAG_ENTRYPOINT] )]
    public function login( array $params ): JsonResponse {
        $data = $this->getCurrentUser()?->serialize();
        $data->token = new \stdClass();
        $data->token->token = $this->getSecurityToken()->getTokenString();
        $data->token->expires = $this->getSecurityToken()->expiresAt()->format('Y-m-d H:i:s');

        return new JsonResponse($data);
    }

}