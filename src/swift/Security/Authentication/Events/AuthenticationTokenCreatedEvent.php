<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Events;


use Swift\Security\Authentication\Token\TokenInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class AuthenticationTokenCreatedEvent
 * @package Swift\Security\Authentication\Events
 */
class AuthenticationTokenCreatedEvent extends Event {

    /**
     * AuthenticationTokenCreatedEvent constructor.
     *
     * @param TokenInterface $token
     */
    public function __construct(
        private TokenInterface $token,
    ) {
    }

    /**
     * @return TokenInterface
     */
    public function getToken(): TokenInterface {
        return $this->token;
    }

    /**
     * @param TokenInterface $token
     */
    public function setToken( TokenInterface $token ): void {
        $this->token = $token;
    }


}