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
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Token\TokenInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class AuthenticationFinishedEvent
 * @package Swift\Security\Authentication\Events
 */
class AuthenticationFinishedEvent extends Event {


    /**
     * AuthenticationSuccessEvent constructor.
     *
     * @param TokenInterface $token
     * @param PassportInterface $passport
     * @param RequestInterface $request
     */
    public function __construct(
        private TokenInterface $token,
        private PassportInterface $passport,
        private RequestInterface $request,
    ) {
    }

    /**
     * @return TokenInterface
     */
    public function getToken(): TokenInterface {
        return $this->token;
    }

    /**
     * @return PassportInterface
     */
    public function getPassport(): PassportInterface {
        return $this->passport;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface {
        return $this->request;
    }


}