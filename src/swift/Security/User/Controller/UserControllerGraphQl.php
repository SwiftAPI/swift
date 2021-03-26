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
use Swift\GraphQl\Attributes\Mutation;
use Swift\GraphQl\Attributes\Query;
use Swift\GraphQl\ContextInterface;
use Swift\GraphQl\Generators\EntityArgumentGenerator;
use Swift\HttpFoundation\Exception\BadRequestException;
use Swift\Kernel\Attributes\Autowire;
use Swift\Model\Entity\Arguments;
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
use Swift\Security\User\Type\UserInput;
use Swift\Security\User\Type\UserType;
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
     * GraphQl endpoint for creating user account
     *
     * @param $userInput
     *
     * @return UserType
     */
    #[Mutation(name: 'UserCreate', type: UserType::class, description: 'Create new user' )]
    public function create( UserInput $userInput ): UserType {
        try {
            $data = $this->userProvider->storeUser(...(array) $userInput)->serialize();
            unset($data->password);
        } catch (UserAlreadyExistsException $exception) {
            throw new BadRequestException(sprintf('User already exists: %s', $exception->getMessage()));
        }

        return new UserType(...(array) $data);
    }

    /**
     * GraphQl endpoint for creating user account
     *
     * @return UserType
     */
    #[Query(name: 'UserMe', isList: false, description: 'Fetch currently authenticated user' )]
    public function me(): UserType {
        // Make sure a user is authenticated
        $this->denyAccessUnlessGranted(
            [AuthorizationTypesEnum::IS_AUTHENTICATED, AuthorizationRolesEnum::ROLE_USER],
            null,
            AccessDecisionManager::STRATEGY_UNANIMOUS
        );

        // Get user data
        $data = $this->getCurrentUser()->serialize();
        unset($data->password);

        return new UserType(...(array)$data);
    }

    /**
     * GraphQl endpoint for creating user account
     *
     * @param int $id
     *
     * @return UserType
     */
    #[Query(name: 'User', type: UserType::class, isList: false, description: 'Fetch user by id' )]
    public function user( int $id ): UserType {
        // Make sure a user is authenticated
        $this->denyAccessUnlessGranted([AuthorizationTypesEnum::IS_AUTHENTICATED, AuthorizationRolesEnum::ROLE_USERS_LIST]);

        // Get user data
        if (!$data = $this->userProvider->getUserById($id)?->serialize()) {
            throw new UserNotFoundException(sprintf('User with id %s not found', $id));
        }
        unset($data->password);

        return new UserType(...(array)$data);
    }

    /**
     * GraphQl endpoint for creating user account
     *
     * @param array $filter
     *
     * @return UserType[]
     */
    #[Query(name: 'Users', type: UserType::class, isList: true, description: 'List all users' )]
    public function users( #[Argument(type: Arguments::class, generator: EntityArgumentGenerator::class, generatorArguments: ['entity' => UserEntity::class])] array $filter ): array {
        // Make sure a user is authenticated
        $this->denyAccessUnlessGranted([AuthorizationRolesEnum::ROLE_USERS_LIST]);

        $state = $filter['where'] ?? array();
        unset($filter['where']);

        if (!$result = $this->userDatabaseStorage->findMany($state, new Arguments(...$filter))) {
            return array();
        }

        $users = array();
        foreach ($result as $value) {
            unset($value->password);
            $users[] = new UserType(...(array)$value);
        }

        return $users;
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
    public function login( LoginInput $credentials ): LoginResponseType {
        // Make sure a direct login occurred instead of a re-authentication or no authentication at all
        $this->authorizationChecker->denyUnlessGranted([AuthorizationTypesEnum::IS_AUTHENTICATED_DIRECTLY]);

        // Fetch user data
        $data = $this->getCurrentUser()->serialize();

        // Append token so client can refer this authenticated session
        $data->token = new TokenType(
            $this->getSecurityToken()->getTokenString(),
            new \DateTime($this->getSecurityToken()->expiresAt()->format('Y-m-d H:i:s')),
        );

        return new LoginResponseType(...(array)$data);
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
    public function forgotPassword( string $email ): ForgotPasswordResponse {
        $this->denyAccessUnlessGranted([AuthorizationRolesEnum::ROLE_GUEST]);

        return new ForgotPasswordResponse();
    }

    #[Mutation(name: 'ResetPassword', description: 'Set new user password')]
    public function resetPassword( ResetPasswordInput $resetPasswordInput ): ResetPasswordResponse {

        return new ResetPasswordResponse();
    }

}