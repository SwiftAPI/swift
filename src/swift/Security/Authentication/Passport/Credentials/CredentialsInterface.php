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

/**
 * Interface CredentialsInterface
 * @package Swift\Security\Authentication\Passport\Credentials
 */
interface CredentialsInterface {

    /**
     * Get credentials
     *
     * @return mixed
     */
    public function getCredential(): mixed;

    /**
     * Validate whether given credentials match
     *
     * @throws InvalidCredentialsException
     */
    public function validateCredentials(): void;

}