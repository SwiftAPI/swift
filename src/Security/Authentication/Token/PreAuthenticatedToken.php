<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Token;

use stdClass;
use Swift\DependencyInjection\Attributes\DI;
use Swift\Security\User\Entity\UserEntity;
use Swift\Security\User\UserInterface;


/**
 * Class PreAuthenticatedToken
 * @package Swift\Security\Authentication\Token
 */
#[DI( autowire: false )]
class PreAuthenticatedToken extends AbstractToken {
    
    /**
     * Token constructor.
     *
     * @param UserInterface $user
     * @param stdClass      $token
     * @param bool          $isAuthenticated
     */
    public function __construct(
        UserInterface $user,
        stdClass      $token,
        bool          $isAuthenticated = true,
    ) {
        foreach ( get_object_vars( $token ) as $var => $value ) {
            if ( $value instanceof UserEntity ) {
                continue;
            }
            if ( property_exists( $this, $var ) ) {
                $this->{$var} = $value;
            }
        }
        
        parent::__construct( $user, $token->scope, $token->accessToken, $isAuthenticated );
    }
    
}