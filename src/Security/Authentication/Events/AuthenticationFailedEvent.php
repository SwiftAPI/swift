<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Events;


use Psr\Http\Message\RequestInterface;
use Swift\Events\AbstractEvent;
use Swift\Security\Authentication\Authenticator\AuthenticatorInterface;
use Swift\Security\Authentication\Exception\AuthenticationException;

/**
 * Class AuthenticationFailedEvent
 * @package Swift\Security\Authentication\Events
 */
class AuthenticationFailedEvent extends AbstractEvent {

    protected static string $eventDescription = 'Authentication has failed. This could be due to an error or access could be denied';
    protected static string $eventLongDescription = '';

    /**
     * AuthenticationSuccessEvent constructor.
     *
     * @param RequestInterface $request
     * @param AuthenticatorInterface $authenticator
     * @param AuthenticationException $authenticationException
     */
    public function __construct(
        private readonly RequestInterface        $request,
        private readonly AuthenticatorInterface  $authenticator,
        private readonly AuthenticationException $authenticationException,
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