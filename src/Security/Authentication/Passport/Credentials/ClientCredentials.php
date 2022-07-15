<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Passport\Credentials;


use Swift\Security\Authentication\Exception\InvalidCredentialsException;
use Swift\Security\User\UserInterface;

/**
 * Class ClientCredentials
 * @package Swift\Security\Authentication\Passport\Credentials
 */
final class ClientCredentials implements CredentialsInterface {
    
    /**
     * ClientCredentials constructor.
     *
     * @param string $credential
     */
    public function __construct(
        private readonly string $credential,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public function getCredential(): string {
        return $this->credential;
    }
    
    /**
     * @inheritDoc
     */
    public function validateCredentials( UserInterface $user ): void {
        if ( $this->getCredential() !== $user->getCredential()->getCredential() ) {
            throw new InvalidCredentialsException( 'Invalid Client Credentials' );
        }
    }
}