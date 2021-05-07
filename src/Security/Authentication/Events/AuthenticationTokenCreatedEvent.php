<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Events;


use Swift\Events\AbstractEvent;
use Swift\Security\Authentication\Token\TokenInterface;

/**
 * Class AuthenticationTokenCreatedEvent
 * @package Swift\Security\Authentication\Events
 */
class AuthenticationTokenCreatedEvent extends AbstractEvent {

    protected static string $eventDescription = 'Authentication token has been created';
    protected static string $eventLongDescription = '';

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