<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Passport\Credentials;


use Swift\Security\Authentication\Exception\InvalidCredentialsException;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\User\UserInterface;

/**
 * Class RefreshTokenCredentials
 * @package Swift\Security\Authentication\Passport\Credentials
 */
final class RefreshTokenCredentials implements CredentialsInterface {

    /**
     * RefreshTokenCredentials constructor.
     *
     * @param \stdClass $token
     */
    public function __construct(
        private \stdClass $token,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getCredential(): string {
        return $this->token->accessToken;
    }

    /**
     * @inheritDoc
     */
    public function validateCredentials( UserInterface $user ): void {
        if ($this->token->expires->getTimestamp() < time()) {
            throw new InvalidCredentialsException('Invalid Refresh Token');
        }
    }
}