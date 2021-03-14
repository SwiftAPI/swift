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
 * Class PasswordCredentials
 * @package Swift\Security\Authentication\Passport\Credentials
 */
class PasswordCredentials implements CredentialsInterface {

    /**
     * PasswordCredentials constructor.
     *
     * @param PasswordCredentialsEncoder|string $password
     */
    public function __construct(
        private PasswordCredentialsEncoder|string $password,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getCredential(): string {
        return $this->password->getEncoded();
    }

    /**
     * @inheritDoc
     */
    public function validateCredentials( UserInterface $user ): void {
        $providedCredentials = $this->password instanceof CredentialEncoderInterface ? $this->password->getEncoded() : $this->password;

        if (!password_verify($providedCredentials, $user->getCredential())) {
            throw new InvalidCredentialsException('Invalid user password');
        }
    }
}