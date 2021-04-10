<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Events;

use Swift\Security\Authentication\Authenticator\AuthenticatorInterface;
use Swift\Security\Authentication\Passport\PassportInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class CheckPassportEvent
 * @package Swift\Security\Authentication\Events
 */
class CheckPassportEvent extends Event {

    /**
     * CheckPassportEvent constructor.
     *
     * @param AuthenticatorInterface $authenticator
     * @param PassportInterface $passport
     */
    public function __construct(
        private AuthenticatorInterface $authenticator,
        private PassportInterface $passport,
    ) {
    }

    /**
     * @return AuthenticatorInterface
     */
    public function getAuthenticator(): AuthenticatorInterface {
        return $this->authenticator;
    }

    /**
     * @return PassportInterface
     */
    public function getPassport(): PassportInterface {
        return $this->passport;
    }


}