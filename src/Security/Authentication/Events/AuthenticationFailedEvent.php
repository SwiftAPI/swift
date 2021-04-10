<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Events;


use Swift\HttpFoundation\RequestInterface;
use Swift\Security\Authentication\Authenticator\AuthenticatorInterface;
use Swift\Security\Authentication\Exception\AuthenticationException;
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Token\TokenInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class AuthenticationFailedEvent
 * @package Swift\Security\Authentication\Events
 */
class AuthenticationFailedEvent extends Event {


    /**
     * AuthenticationSuccessEvent constructor.
     *
     * @param RequestInterface $request
     * @param AuthenticatorInterface $authenticator
     * @param AuthenticationException $authenticationException
     */
    public function __construct(
        private RequestInterface $request,
        private AuthenticatorInterface $authenticator,
        private AuthenticationException $authenticationException,
    ) {
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface {
        return $this->request;
    }

    /**
     * @return AuthenticatorInterface
     */
    public function getAuthenticator(): AuthenticatorInterface {
        return $this->authenticator;
    }

    /**
     * @return AuthenticationException
     */
    public function getAuthenticationException(): AuthenticationException {
        return $this->authenticationException;
    }




}