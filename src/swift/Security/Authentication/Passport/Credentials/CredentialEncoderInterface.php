<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Passport\Credentials;

/**
 * Interface CredentialEncoderInterface
 * @package Swift\Security\Authentication\Passport\Credentials
 */
interface CredentialEncoderInterface {

    /**
     * Get encoded credentials
     *
     * @return string
     */
    public function getEncoded(): string;

    /**
     * Credentials to string
     *
     * @return string
     */
    public function __toString(): string;

}