<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Passport;

use Swift\Security\Authentication\Passport\Credentials\CredentialsInterface;
use Swift\Security\Authentication\Token\Token;
use Swift\Security\Authentication\Token\TokenInterface;

/**
 * Class Passport
 * @package Swift\Security\Authentication\Passport
 */
class Passport implements PassportInterface {

    /**
     * Passport constructor.
     *
     * @param UserInterface $user
     * @param CredentialsInterface $credentials
     */
    public function __construct(
        private UserInterface $user,
        private CredentialsInterface $credentials,
    ) {
        $this->credentials->validateCredentials();
    }

    public function getToken(): TokenInterface {
        return new Token();
    }
}