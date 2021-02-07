<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Authenticator;

use Psr\Http\Message\RequestInterface;
use Swift\HttpFoundation\ResponseInterface;
use Swift\Kernel\Attributes\DI;
use Swift\Security\Authentication\DiTags;
use Swift\Security\Authentication\Exception\AuthenticationException;
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Token\TokenInterface;

/**
 * Class AuthenticatorInterface
 * @package Swift\Security\Authentication\Authenticator
 */
#[DI(tags: [DiTags::SECURITY_AUTHENTICATOR])]
interface AuthenticatorInterface {

    /**
     * Confirm whether authenticator can authenticate current request
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function supports( RequestInterface $request ): bool;

    /**
     * Authenticate given request
     *
     * @param RequestInterface $request
     *
     * @return PassportInterface    PassportInterface representing the user
     *
     * @throws AuthenticationException  When authentication fails. In onAuthenticationFailure you will be able to further deal with this and generate a fitting response
     */
    public function authenticate( RequestInterface $request ): PassportInterface;

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
     * @param RequestInterface $request
     * @param TokenInterface $token
     *
     * @return ResponseInterface|null   Null will make the request move on. By returning a response this response will be used and the request will not move on
     */
    public function onAuthenticationSuccess( RequestInterface $request, TokenInterface $token ): ?ResponseInterface;

    /**
     * Called on authentication failure.
     *
     * @param RequestInterface $request
     * @param AuthenticationException $authenticationException
     *
     * @return ResponseInterface|null   Null will ignore the failure and move on. By returning a response this response will be used and the request will not move on
     */
    public function onAuthenticationFailure( RequestInterface $request, AuthenticationException $authenticationException ): ?ResponseInterface;

}