<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Passport\Credentials;

use Swift\Security\Authentication\Exception\InvalidCredentialsException;
use Swift\Security\Authentication\Passport\UserInterface;

/**
 * Class ApiTokenCredentials
 * @package Swift\Security\Authentication\Passport\Credentials
 */
class ApiTokenCredentials implements CredentialsInterface {

    /**
     * ApiTokenCredentials constructor.
     *
     * @param UserInterface|string $user
     * @param CredentialEncoderInterface|string $providedCredential
     */
    public function __construct(
        private UserInterface|string $user,
        private CredentialEncoderInterface|string $providedCredential,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getCredential(): string {
        return $this->providedCredential;
    }

    /**
     * @inheritDoc
     */
    public function validateCredentials(): void {
        $userCredential = $this->user instanceof UserInterface ? $this->user->getCredential() : $this->user;
        $providedCredentials = $this->providedCredential instanceof CredentialEncoderInterface ? $this->providedCredential->getEncoded() : $this->providedCredential;

        if ($userCredential !== $providedCredentials) {
            throw new InvalidCredentialsException('Invalid Api token provided');
        }
    }
}