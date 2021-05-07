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
use Swift\Security\Authentication\Authenticator\AuthenticatorInterface;
use Swift\Security\Authentication\Passport\PassportInterface;

/**
 * Class CheckPassportEvent
 * @package Swift\Security\Authentication\Events
 */
class CheckPassportEvent extends AbstractEvent {

    protected static string $eventDescription = 'Passport has been created. Run any validations against passport or add data to passport';
    protected static string $eventLongDescription = '';

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