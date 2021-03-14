<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Authenticator\OAuth;


use Swift\HttpFoundation\RequestInterface;
use Swift\HttpFoundation\ResponseInterface;
use Swift\Security\Authentication\Authenticator\AuthenticatorEntrypointInterface;
use Swift\Security\Authentication\Authenticator\AuthenticatorInterface;
use Swift\Security\Authentication\Exception\AuthenticationException;
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Token\TokenInterface;

/**
 * Class OAuthRefreshTokenAuthenticator
 * @package Swift\Security\Authentication\Authenticator\OAuth
 */
class OAuthRefreshTokenAuthenticator implements AuthenticatorInterface, AuthenticatorEntrypointInterface {

    /**
     * @inheritDoc
     */
    public function supports( RequestInterface $request ): bool {
        // TODO: Implement supports() method.
    }

    /**
     * @inheritDoc
     */
    public function authenticate( RequestInterface $request ): PassportInterface {
        // TODO: Implement authenticate() method.
    }

    /**
     * @inheritDoc
     */
    public function createAuthenticatedToken( PassportInterface $passport ): TokenInterface {
        // TODO: Implement createAuthenticatedToken() method.
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess( RequestInterface $request, TokenInterface $token ): ?ResponseInterface {
        // TODO: Implement onAuthenticationSuccess() method.
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure( RequestInterface $request, AuthenticationException $authenticationException ): ?ResponseInterface {
        // TODO: Implement onAuthenticationFailure() method.
    }
}