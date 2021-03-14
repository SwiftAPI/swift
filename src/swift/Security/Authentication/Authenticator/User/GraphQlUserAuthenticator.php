<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Authenticator\User;


use Swift\HttpFoundation\RequestInterface;
use Swift\HttpFoundation\JsonResponse;
use Swift\HttpFoundation\ResponseInterface;
use Swift\Kernel\Attributes\Autowire;
use Swift\Model\EntityInterface;
use Swift\Security\Authentication\Authenticator\AuthenticatorInterface;
use Swift\Security\Authentication\Exception\AuthenticationException;
use Swift\Security\Authentication\Passport\Credentials\PasswordCredentials;
use Swift\Security\Authentication\Passport\Passport;
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Token\AuthenticatedToken;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\User\UserProviderInterface;

/**
 * Class UserAuthenticator
 * @package Swift\Security\Authentication\Authenticator
 */
#[Autowire]
final class GraphQlUserAuthenticator implements AuthenticatorInterface {

    private array|null $parsed;

    /**
     * AccessTokenAuthenticator constructor.
     *
     * @param EntityInterface $accessTokenEntity
     * @param UserProviderInterface $userProvider
     */
    public function __construct(
        private EntityInterface $accessTokenEntity,
        private UserProviderInterface $userProvider,
    ) {
    }

    /**
     * Support Bearer tokens
     *
     * @inheritDoc
     */
    public function supports( RequestInterface $request ): bool {
        if ($request->getUri()->getPath() !== '/graphql') {
            return false;
        }

        return $request->getContent()->has('userLogin');
    }

    /**
     * @inheritDoc
     */
    public function authenticate( RequestInterface $request ): PassportInterface {
        $username = $request->getContent()->get('userLogin')['username'] ?? null;
        $password = $request->getContent()->get('userLogin')['password'] ?? null;

        if (!$username || !$password) {
            throw new AuthenticationException('Invalid user credentials');
        }

        if (!$user = $this->userProvider->getUserByUsername($username)) {
            throw new AuthenticationException('No user found with given credentials');
        }

        return new Passport($user, new PasswordCredentials( $password ));
    }

    /**
     * @inheritDoc
     */
    public function createAuthenticatedToken( PassportInterface $passport ): TokenInterface {
        return new AuthenticatedToken(
            user: $passport->getUser(),
            scope: TokenInterface::SCOPE_ACCESS_TOKEN,
            token: null,
            isAuthenticated: true,
        );
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess( RequestInterface $request, TokenInterface $token ): ?ResponseInterface {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure( RequestInterface $request, AuthenticationException $authenticationException ): ?ResponseInterface {
        $response = new \stdClass();
        $response->message = $authenticationException->getMessage();
        $response->code = $authenticationException->getCode();
        return new JsonResponse($response, $authenticationException->getCode());
    }

}