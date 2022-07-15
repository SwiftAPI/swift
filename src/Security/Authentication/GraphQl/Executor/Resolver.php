<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\GraphQl\Executor;


use GraphQL\Type\Definition\ResolveInfo;
use Swift\GraphQl\Exception\FieldUnAuthorizedException;
use Swift\Security\Authorization\AuthorizationRole;

class Resolver extends \Swift\GraphQl\Executor\Resolver\AbstractResolver {
    
    public function resolveAccessToken( \Swift\Security\Authentication\Entity\AccessTokenEntity $value, $args, $context, ResolveInfo $info ): \Swift\Security\Authentication\Entity\AccessTokenEntity {
        if ( $this->getCurrentUser()->getUuid() === $value->user->getUuid() ) {
            return $value;
        }
        
        if ( ! $this->authorizationChecker->isGranted( [ AuthorizationRole::ROLE_ADMIN ] ) ) {
            throw FieldUnAuthorizedException::fieldUnAuthorized( $info->fieldName, $info->parentType->name );
        }
        
        return $value;
    }
    
    public function resolveAccessTokens( mixed $value, $args, $context, ResolveInfo $info ): mixed {
        if ( ! $this->authorizationChecker->isGranted( [ AuthorizationRole::ROLE_ADMIN ] ) ) {
            throw FieldUnAuthorizedException::fieldUnAuthorized( $info->fieldName, $info->parentType->name );
        }
        
        return $value;
    }
    
}