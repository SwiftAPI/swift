<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\Authentication\Authenticator\Rest;


use Swift\DependencyInjection\Attributes\Autowire;
use Swift\HttpFoundation\JsonResponse;
use Swift\HttpFoundation\ResponseInterface;
use Swift\Router\Router;
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
 * @package Swift\Security\Authentication\Authenticator\Rest
 */
#[Autowire]
final class ForgotPasswordAuthenticator implements AuthenticatorInterface, AuthenticatorEntrypointInterface {

    public const TAG_FORGOT_PASSWORD = 'TAG_FORGOT_PASSWORD';
    
    /**
     * ForgotPasswordAuthenticator constructor.
     *
     * @param UserProviderInterface $userProvider
     * @param \Swift\Router\Router  $router
     */
    public function __construct(
        private readonly UserProviderInterface $userProvider,
        private readonly Router                $router,
    ) {
    }

    /**
     * Support if Route is tagged with TAG_FORGOT_PASSWORD and an email is provided
     *
     * @inheritDoc
     */
    public function supports( \Psr\Http\Message\RequestInterface $request ): bool {
        return $this->router->getCurrentRoute()->getTags()->has(self::TAG_FORGOT_PASSWORD) && $request->getContent()->has('email');
    }

    /**
     * @inheritDoc
     */
    public function authenticate( \Psr\Http\Message\RequestInterface $request ): PassportInterface {
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
    public function onAuthenticationSuccess( \Psr\Http\Message\RequestInterface $request, TokenInterface $token ): ?ResponseInterface {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure( \Psr\Http\Message\RequestInterface $request, AuthenticationException $authenticationException ): ?ResponseInterface {
        $response = new \stdClass();
        $response->message = $authenticationException->getMessage();
        $response->code = $authenticationException->getCode();
        return new JsonResponse($response, $authenticationException->getCode());
    }
    
}