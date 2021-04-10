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
 * Class PasswordCredentialsEncoder
 * @package Swift\Security\Authentication\Passport\Credentials
 */
class PasswordCredentialsEncoder implements CredentialEncoderInterface {

    /**
     * PasswordCredentialsEncoder constructor.
     *
     * @param string $password
     */
    public function __construct(
        private string $password,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getEncoded(): string {
        $options = array(
            'cost' => 12,
        );

        return password_hash($this->password, PASSWORD_BCRYPT, $options);
    }

    public function getPlain(): string {
        return $this->password;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string {
        return $this->getEncoded();
    }
}