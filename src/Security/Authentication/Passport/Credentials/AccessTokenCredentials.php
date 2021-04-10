<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Passport\Credentials;


use DateTime;
use stdClass;
use Swift\HttpFoundation\Response;
use Swift\Security\Authentication\Exception\InvalidCredentialsException;
use Swift\Security\User\UserInterface;

/**
 * Class AccessTokenCredentials
 * @package Swift\Security\Authentication\Passport\Credentials
 */
final class AccessTokenCredentials implements CredentialsInterface {

    /**
     * ApiTokenCredentials constructor.
     *
     * @param stdClass $providedCredential
     */
    public function __construct(
        private stdClass $providedCredential,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getCredential(): string {
        return $this->providedCredential->accessToken;
    }

    /**
     * @inheritDoc
     */
    public function validateCredentials( UserInterface $user ): void {
        /** @var DateTime $expires */
        $expires = $this->providedCredential->expires;
        if (!$this->providedCredential->accessToken || ($expires->getTimestamp() < time())) {
            throw new InvalidCredentialsException('Token has expired', Response::HTTP_UNAUTHORIZED);
        }
    }
}