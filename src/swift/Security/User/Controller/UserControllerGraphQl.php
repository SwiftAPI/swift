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
use Swift\GraphQl\Attributes\Argument;
use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\Mutation;
use Swift\GraphQl\Attributes\Query;
use Swift\GraphQl\ContextInterface;
use Swift\GraphQl\Generators\EntityArgumentGenerator;
use Swift\GraphQl\Types\PageInfoType;
use Swift\HttpFoundation\Exception\BadRequestException;
use Swift\Kernel\Attributes\Autowire;
use Swift\Model\Entity\Arguments;
use Swift\Model\Types\ArgumentsType;
use Swift\Router\Attributes\Route;
use Swift\Router\Types\RouteMethodEnum;
use Swift\Security\Authentication\Types\TokenType;
use Swift\Security\Authorization\AccessDecisionManager;
use Swift\Security\Authorization\AuthorizationRolesEnum;
use Swift\Security\Authorization\AuthorizationTypesEnum;
use Swift\Security\User\Entity\UserEntity;
use Swift\Security\User\Exception\UserAlreadyExistsException;
use Swift\Security\User\Exception\UserNotFoundException;
use Swift\Security\User\Type\ForgotPasswordResponse;
use Swift\Security\User\Type\LoginInput;
use Swift\Security\User\Type\LoginResponseType;
use Swift\Security\User\Type\ResetPasswordInput;
use Swift\Security\User\Type\ResetPasswordResponse;
use Swift\Security\User\Type\UserConnection;
use Swift\Security\User\Type\UserEdge;
use Swift\Security\User\Type\UserInput;
use Swift\Security\User\Type\UserType;
use Swift\Security\User\User;
use Swift\Security\User\UserProviderInterface;
use Swift\Security\User\UserStorageInterface;

/**
 * Class UserControllerGraphQl
 * @package Swift\Security\User\Controller
 */
#[Route(method: [RouteMethodEnum::GET, RouteMethodEnum::POST], route: '/users/', name: 'security.user'), Autowire]
class UserControllerGraphQl extends AbstractController {

    /**
     * UserControllerGraphQl constructor.
     *
     * @param UserProviderInterface $userProvider
     * @param ContextInterface $context
     * @param UserStorageInterface $userDatabaseStorage
     */
    public function __construct(
        private UserProviderInterface $userProvider,
        private ContextInterface $context,
        private UserStorageInterface $userDatabaseStorage,
    ) {
    }

    /**
     * Node field resolver callback function for UserType
     *
     * @param string|int $id
     * @param ContextInterface $context
     *
     * @return UserType
     */
    public function getUserTypeByNode( string|int $id, ContextInterface $context ): UserType {
        // Make sure a user is authenticated
        $this->denyAccessUnlessGranted([AuthorizationTypesEnum::IS_AUTHENTICATED, AuthorizationRolesEnum::ROLE_USERS_LIST]);

        // Get user data
        if (!$data = $this->userProvider->getUserById((int) $id)?->serialize()) {
            throw new UserNotFoundException(sprintf('User with id %s not found', $id));
        }

        return new UserType(...(array) $data);
    }

    /**
     * GraphQl endpoint for creating user account
     *
     * @param $userInput
     *
     * @return UserEdge
     */
    #[Mutation(name: 'UserCreate', type: UserEdge::class, description: 'Create new user' )]
    public function create( UserInput $userInput ): UserEdge {
        try {
            $data = $this->userProvider->storeUser(...(array) $userInput)->serialize();
        } catch (UserAlreadyExistsException $exception) {
            throw new BadRequestException(sprintf('User already exists: %s', $exception->getMessage()));
        }

        return new UserEdge($data->id, new UserType(...(array) $data));
    }

    /**
     * GraphQl endpoint for creating user account
     *
     * @return UserEdge
     */
    #[Query(name: 'UserMe', isList: false, description: 'Fetch currently authenticated user' )]
    public function me(): UserEdge {
        // Make sure a user is authenticated
        $this->denyAccessUnlessGranted(
            [AuthorizationTypesEnum::IS_AUTHENTICATED, AuthorizationRolesEnum::ROLE_USER],
            null,
            AccessDecisionManager::STRATEGY_UNANIMOUS
        );

        // Get user data
        $data = $this->getCurrentUser()->serialize();

        return new UserEdge($data->id, new UserType(...(array) $data));
    }

    /**
     * GraphQl endpoint for creating user account
     *
     * @param string $id
     *
     * @return UserEdge
     */
    #[Query(name: 'User', isList: false, description: 'Fetch user by id' )]
    public function user( string $id ): UserEdge {
        // Make sure a user is authenticated
        $this->denyAccessUnlessGranted([AuthorizationTypesEnum::IS_AUTHENTICATED, AuthorizationRolesEnum::ROLE_USERS_LIST]);

        // Get user data
        if (!$data = $this->userProvider->getUserById((int) $id)?->serialize()) {
            throw new UserNotFoundException(sprintf('User with id %s not found', $id));
        }

        return new UserEdge($data->id, new UserType(...(array) $data));
    }

    /**
     * GraphQl endpoint for listing users
     *
     * @param array $filter
     *
     * @return UserConnection
     */
    #[Query(name: 'Users', description: 'List all users' )]
    public function users( #[Argument(type: ArgumentsType::class, generator: EntityArgumentGenerator::class, generatorArguments: ['entity' => UserEntity::class])] array $filter ): UserConnection {
        // Make sure a user is authenticated
        $this->denyAccessUnlessGranted([AuthorizationRolesEnum::ROLE_USERS_LIST]);

        $filter ??= array();
        $state = $filter['where'] ?? array();
        unset($filter['where']);
        $argumentsType = new ArgumentsType(...$filter);

        if (!$result = $this->userDatabaseStorage->findMany($state, $argumentsType->toArgument())) {
            return new UserConnection($result);
        }


        return new UserConnection($result);
    }

    /**
     * GraphQl endpoint for listing users
     *
     * @param array|null $filter
     *
     * @return UserConnection
     */
    #[Query(name: 'UsersRelay', description: 'List all users' )]
    public function usersRelay( #[Argument(type: ArgumentsType::class, generator: EntityArgumentGenerator::class, generatorArguments: ['entity' => UserEntity::class])] array|null $filter = null ): UserConnection {
        $filter ??= array();
        $state = $filter['where'] ?? array();
        unset($filter['where']);
        $argumentsType = new ArgumentsType(...$filter);

        if (!$result = $this->userDatabaseStorage->findMany($state, $argumentsType->toArgument())) {
            return new UserConnection($result);
        }


        return new UserConnection($result);
    }

    /**
     * GraphQl user authentication endpoint
     *
     * Authentication already occurs on the security component. So all that needs to be done is return the currently authenticated user
     *
     * @param $credentials
     *
     * @return LoginResponseType    User data and session token
     */
    #[Mutation(name: 'UserLogin', type: LoginResponseType::class, description: 'User login endpoint (username + password)' )]
    public function login( #[Argument(description: 'User credentials')] LoginInput $credentials ): LoginResponseType {
        // Make sure a direct login occurred instead of a re-authentication or no authentication at all
        $this->authorizationChecker->denyUnlessGranted([AuthorizationTypesEnum::IS_AUTHENTICATED_DIRECTLY]);

        // Fetch user data
        $data = $this->getCurrentUser()->serialize();
        $user = new UserEdge($data->id, new UserType(...(array) $data));

        // Append token so client can refer this authenticated session
        $token = new TokenType(
            $this->getSecurityToken()->getTokenString(),
            new \DateTime($this->getSecurityToken()->expiresAt()->format('Y-m-d H:i:s')),
        );

        return new LoginResponseType($user, $token);
    }

    /**
     * Forgot password authentication endpoint
     *
     * Make sure no user authenticated (hence AuthorizationRolesEnum::ROLE_GUEST)
     *
     * @param string $email
     *
     * @return ForgotPasswordResponse
     */
    #[Mutation(name: 'ForgotPassword', description: 'Request new password')]
    public function forgotPassword( #[Argument(description: 'E-mail to request new password for')] string $email ): ForgotPasswordResponse {
        $this->denyAccessUnlessGranted([AuthorizationRolesEnum::ROLE_GUEST]);

        return new ForgotPasswordResponse();
    }

    #[Mutation(name: 'ResetPassword', description: 'Set new user password')]
    public function resetPassword( ResetPasswordInput $resetPasswordInput ): ResetPasswordResponse {

        return new ResetPasswordResponse();
    }

}