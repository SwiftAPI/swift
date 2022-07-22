<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\Authentication\Passport\Stamp;


class ResetPasswordStamp implements \Swift\Security\Authentication\Passport\Stamp\StampInterface {
    
    public function __construct(
        protected readonly string $token,
        protected readonly string $password,
    ) {
    }
    
    /**
     * @return string
     */
    public function getToken(): string {
        return $this->token;
    }
    
    /**
     * @return string
     */
    public function getPassword(): string {
        return $this->password;
    }
    
    
    
}