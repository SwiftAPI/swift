<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\Controller;

use Swift\Controller\AbstractController;
use Swift\Dbal\Exceptions\DatabaseException;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\HttpFoundation\Exception\BadRequestException;
use Swift\HttpFoundation\Exception\InternalErrorException;
use Swift\HttpFoundation\JsonResponse;
use Swift\HttpFoundation\Response;
use Swift\Orm\Entity\Arguments;
use Swift\Orm\EntityManagerInterface;
use Swift\Router\Attributes\Route;
use Swift\Router\RouteParameterBag;
use Swift\Router\Types\RouteMethod;
use Swift\Security\Authorization\AuthorizationRole;
use Swift\Security\Authorization\AuthorizationType;
use Swift\Security\User\Authentication\Authenticator\Rest\ForgotPasswordAuthenticator;
use Swift\Security\User\Authentication\Authenticator\Rest\ResetPasswordAuthenticator;
use Swift\Security\User\Entity\UserEntity;
use Swift\Security\User\Exception\UserAlreadyExistsException;
use Swift\Security\User\Exception\UserNotFoundException;
use Swift\Security\User\UserProviderInterface;

/**
 * Class UserControllerRest
 * @package Swift\Security\User\Controller
 */
#[Route( method: [ RouteMethod::GET, RouteMethod::POST ], route: '/users/', name: 'security.user' ), Autowire]
class UserController extends AbstractController {
    
    /**
     * UserController constructor.
     *
     * @param UserProviderInterface             $userProvider
     * @param \Swift\Orm\EntityManagerInterface $entityManager
     */
    public function __construct(
        private readonly UserProviderInterface  $userProvider,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }
    
    /**
     * Create user account endpoint
     *
     * @param RouteParameterBag $params
     *
     * @return JsonResponse
     */
    #[Route( method: RouteMethod::POST, route: '/create/', name: 'security.user.create' )]
    public function create( RouteParameterBag $params ): JsonResponse {
        $request = $this->getRequest()->getContent();
        
        $username  = $request->get( 'username' );
        $password  = $request->get( 'password' );
        $email     = $request->get( 'email' );
        $firstname = $request->get( 'firstname' );
        $lastname  = $request->get( 'lastname' );
        
        if ( ! $username || ! $password || ! $email || ! $firstname || ! $lastname ) {
            throw new BadRequestException( 'Invalid user input' );
        }
        
        try {
            $data = $this->userProvider->storeUser( $username, $password, $email, $firstname, $lastname )->serialize();
            unset( $data->password );
        } catch ( UserAlreadyExistsException $exception ) {
            throw new BadRequestException( sprintf( 'User already exists: %s', $exception->getMessage() ) );
        }
        
        return new JsonResponse( $data );
    }
    
    /**
     * Return currently authenticated user. For this it is required that a user is authenticated
     *
     * @param RouteParameterBag $params
     *
     * @return JsonResponse
     */
    #[Route( method: RouteMethod::GET, route: '/me/', name: 'security.users.me', isGranted: [ AuthorizationType::IS_AUTHENTICATED ] )]
    public function me( RouteParameterBag $params ): JsonResponse {
        $data = $this->getCurrentUser()->serialize();
        unset( $data->password );
        
        return new JsonResponse( $data );
    }
    
    /**
     * Fetch user by id
     *
     * User must have permission to list users (ROLE_USERS_LIST)
     * To fetch current user data, use the /me/ endpoint. This does not require ROLE_USERS_LIST permission
     *
     * @param RouteParameterBag $params
     *
     * @return JsonResponse
     */
    #[Route( method: [ RouteMethod::GET ], route: '/[i:user_id]/', name: 'security.users.single', isGranted: [ AuthorizationRole::ROLE_USERS_LIST ] )]
    public function user( RouteParameterBag $params ): JsonResponse {
        // Get user data
        if ( ! $data = $this->userProvider->getUserById( $params->get( 'user_id' )->getValue() )?->serialize() ) {
            throw new UserNotFoundException( sprintf( 'User with id %s not found', $params->get( 'user_id' )->getValue() ) );
        }
        unset( $data->password );
        
        return new JsonResponse( $data );
    }
    
    /**
     * REST users list endpoint
     * Endpoint accepts the same parameters as GraphQl endpoint for filtering and pagination
     *
     * User must have permission to list users (ROLE_USERS_LIST)
     *
     * @param RouteParameterBag $params
     *
     * @return JsonResponse
     */
    #[Route( method: [ RouteMethod::GET, RouteMethod::POST ], route: '/list/', name: 'security.users.list', isGranted: [ AuthorizationRole::ROLE_USERS_LIST ] )]
    public function list( RouteParameterBag $params ): JsonResponse {
        $filter = $this->getRequest()->getContent()->getIterator()->getArrayCopy();
        $state  = $filter[ 'where' ] ?? [];
        unset( $filter[ 'where' ] );
        $filter = array_filter( $filter, static fn( $item ): bool => ! empty( $item ) );
        
        $result = $this->entityManager->findMany( UserEntity::class, $state, new Arguments( ...$filter ) );
        
        $users = [];
        foreach ( $result as $value ) {
            unset( $value->password );
            $value->created  = $value->created->format( 'Y-m-d H:i:s' );
            $value->modified = $value->modified->format( 'Y-m-d H:i:s' );
            $users[]         = $value->getSimple();
        }
        
        return new JsonResponse( $users );
    }
    
    /**
     * Rest user authentication endpoint
     *
     * Authentication already occurs on the security component. So all that needs to be done is return the currently authenticated user
     *
     * Only a direct login is valid here. Re-authentication or no authentication is not valid. This is already cover through isGranted in the route (validated by the firewall)
     *
     * @param RouteParameterBag $params
     *
     * @return JsonResponse
     */
    #[Route( method: [ RouteMethod::POST ], route: '/login/', name: 'security.user.login', isGranted: [ AuthorizationType::IS_AUTHENTICATED_DIRECTLY ], tags: [ Route::TAG_ENTRYPOINT ] )]
    public function login( RouteParameterBag $params ): JsonResponse {
        $data                 = $this->getCurrentUser()?->serialize();
        $data->token          = new \stdClass();
        $data->token->token   = $this->getSecurityToken()->getTokenString();
        $data->token->expires = $this->getSecurityToken()->expiresAt()->format( 'Y-m-d H:i:s' );
        
        return new JsonResponse( $data );
    }
    
    /**
     * Forgot password authentication endpoint
     *
     * Make sure no user authenticated (hence AuthorizationRole::ROLE_GUEST)
     *
     * @param RouteParameterBag $params
     *
     * @return JsonResponse
     */
    #[Route( method: [ RouteMethod::POST ], route: '/password/forgot/', name: 'security.user.password.forgot', isGranted: [ AuthorizationRole::ROLE_GUEST ], tags: [ Route::TAG_ENTRYPOINT, ForgotPasswordAuthenticator::TAG_FORGOT_PASSWORD ] )]
    public function forgotPassword( RouteParameterBag $params ): JsonResponse {
        return new JsonResponse(
            [
                'message' => 'Successfully requested reset password token. The user has been notified.',
                'code'    => Response::HTTP_OK,
            ]
        );
    }
    
    /**
     * Reset password endpoint
     *
     * @param RouteParameterBag $params
     *
     * @return JsonResponse
     */
    #[Route( method: [ RouteMethod::POST ], route: '/password/reset/', name: 'security.user.password.reset', isGranted: [ AuthorizationRole::ROLE_CHANGE_PASSWORD ], tags: [ Route::TAG_ENTRYPOINT, ResetPasswordAuthenticator::TAG_RESET_PASSWORD ] )]
    public function resetPassword( RouteParameterBag $params ): JsonResponse {
        try {
            $this->getCurrentUser()->set(
                [
                    'password' => $this->getRequest()->getContent()->get( 'newPassword' ),
                ]
            );
        } catch ( DatabaseException ) {
            throw new InternalErrorException();
        }
        
        return new JsonResponse(
            [
                'message' => 'Successfully reset password',
                'code'    => Response::HTTP_OK,
            ]
        );
    }
    
}