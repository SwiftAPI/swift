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
use Swift\Router\RouterInterface;
use Swift\Security\Authentication\Authenticator\AuthenticatorEntrypointInterface;
use Swift\Security\Authentication\Authenticator\AuthenticatorInterface;
use Swift\Security\Authentication\Exception\AuthenticationException;
use Swift\Security\Authentication\Passport\Credentials\EmailCredentials;
use Swift\Security\Authentication\Passport\Passport;
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Token\ResetPasswordToken;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\User\UserProviderInterface;

/**
 * Class ForgotPasswordAuthenticator
 * @package Swift\Security\Authentication\Authenticator
 */
#[Autowire]
final class ForgotPasswordAuthenticator implements AuthenticatorInterface, AuthenticatorEntrypointInterface {

    public const TAG_FORGOT_PASSWORD = 'TAG_FORGOT_PASSWORD';

    /**
     * AccessTokenAuthenticator constructor.
     *
     * @param EntityInterface $accessTokenEntity
     * @param UserProviderInterface $userProvider
     * @param RouterInterface $router
     */
    public function __construct(
        private EntityInterface $accessTokenEntity,
        private UserProviderInterface $userProvider,
        private RouterInterface $router,
    ) {
    }

    /**
     * Support if Route is tagged with TAG_FORGOT_PASSWORD and an email is provided
     *
     * @inheritDoc
     */
    public function supports( RequestInterface $request ): bool {
        return $this->router->getCurrentRoute()->getTags()->has(self::TAG_FORGOT_PASSWORD) && $request->getContent()->has('email');
    }

    /**
     * @inheritDoc
     */
    public function authenticate( RequestInterface $request ): PassportInterface {
        if (!$user = $this->userProvider->getUserByEmail($request->getContent()->get('email'))) {
            throw new AuthenticationException('No user found with given credentials');
        }

        return new Passport($user, new EmailCredentials( $request->getContent()->get('email') ));
    }

    /**
     * @inheritDoc
     */
    public function createAuthenticatedToken( PassportInterface $passport ): TokenInterface {
        return new ResetPasswordToken(
            user: $passport->getUser(),
            scope: TokenInterface::SCOPE_RESET_PASSWORD,
            token: null,
            isAuthenticated: false,
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