<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Passport\Credentials;


use Swift\Security\Authentication\Exception\InvalidCredentialsException;
use Swift\Security\User\UserInterface;

/**
 * Class NullCredentials
 * @package Swift\Security\Authentication\Passport\Credentials
 */
class NullCredentials implements CredentialsInterface {

    /**
     * @inheritDoc
     */
    public function getCredential(): mixed {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateCredentials( UserInterface $user ): void {}
}