<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Authenticator;

use Swift\DependencyInjection\Attributes\DI;
use Swift\HttpFoundation\ResponseInterface;
use Swift\Security\Authentication\DiTags;
use Swift\Security\Authentication\Exception\AuthenticationException;
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\WebSocket\HttpFoundation\MessageInterface;

/**
 * Class AuthenticatorInterface
 * @package Swift\Security\Authentication\Authenticator
 */
#[DI(tags: [DiTags::SECURITY_AUTHENTICATOR])]
interface AuthenticatorInterface {

    /**
     * Confirm whether authenticator can authenticate current request
     *
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return bool
     */
    public function supports( \Psr\Http\Message\RequestInterface $request ): bool;

    /**
     * Authenticate given request
     *
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return PassportInterface    PassportInterface representing the user
     *
     * @throws AuthenticationException  When authentication fails. In onAuthenticationFailure you will be able to further deal with this and generate a fitting response
     */
    public function authenticate( \Psr\Http\Message\RequestInterface $request ): PassportInterface;

    /**
     * Create an authenticated token based on given passport
     *
     * @param PassportInterface $passport
     *
     * @return TokenInterface
     */
    public function createAuthenticatedToken( PassportInterface $passport ): TokenInterface;
    
    /**
     * Called when successfully authenticated.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param TokenInterface                     $token
     *
     * @return \Swift\HttpFoundation\ResponseInterface|\Swift\WebSocket\HttpFoundation\MessageInterface|null Null will make the request move on. By returning a response this response will be used and the request will not move on
     */
    public function onAuthenticationSuccess( \Psr\Http\Message\RequestInterface $request, TokenInterface $token ): ResponseInterface|MessageInterface|null;
    
    /**
     * Called on authentication failure.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param AuthenticationException            $authenticationException
     *
     * @return \Swift\HttpFoundation\ResponseInterface|\Swift\WebSocket\HttpFoundation\MessageInterface|null Null will ignore the failure and move on. By returning a response this response will be used and the request will not move on
     */
    public function onAuthenticationFailure( \Psr\Http\Message\RequestInterface $request, AuthenticationException $authenticationException ): ResponseInterface|MessageInterface|null;

}