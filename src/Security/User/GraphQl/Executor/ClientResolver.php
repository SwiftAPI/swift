<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\GraphQl\Executor;


use GraphQL\Type\Definition\ResolveInfo;
use JetBrains\PhpStorm\ArrayShape;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\GraphQl\Exception\FieldUnAuthorizedException;
use Swift\HttpFoundation\Response;
use Swift\Orm\EntityManagerInterface;
use Swift\Security\Authorization\AuthorizationRole;
use Swift\Security\User\Entity\UserEntity;

#[Autowire]
class ClientResolver extends \Swift\GraphQl\Executor\Resolver\AbstractResolver {
    
    public function __construct(
        protected EntityManagerInterface $entityManager,
    ) {
    }
    
    public function resolveClient( \Swift\Security\User\Entity\OauthClientsEntity $value, $args, $context, ResolveInfo $info ): \Swift\Security\User\Entity\OauthClientsEntity {
        if ( $this->getCurrentUser()->getUuid() === $value->getUuid() ) {
            return $value;
        }
        
        if ( ! $this->authorizationChecker->isGranted( [ AuthorizationRole::ROLE_USERS_LIST ] ) ) {
            throw FieldUnAuthorizedException::fieldUnAuthorized( $info->fieldName, $info->parentType->name );
        }
        
        return $value;
    }
    
    public function resolveClients( mixed $value, $args, $context, ResolveInfo $info ): mixed {
        if ( ! $this->authorizationChecker->isGranted( [ AuthorizationRole::ROLE_USERS_LIST ] ) ) {
            throw FieldUnAuthorizedException::fieldUnAuthorized( $info->fieldName, $info->parentType->name );
        }
        
        return $value;
    }
    
    public function resolveAuthTokenGet( mixed $value, $args, $context, ResolveInfo $info ): array {
        if ( ! $this->authorizationChecker->isGranted( [ AuthorizationRole::ROLE_CLIENT ] ) ) {
            throw FieldUnAuthorizedException::fieldUnAuthorized( $info->fieldName, $info->parentType->name );
        }
        
        return [
            'accessToken'  => $this->getSecurityToken()->getTokenString(),
            'expires'      => $this->getSecurityToken()->expiresAt(),
            'refreshToken' => $this->getSecurityToken()->getRefreshToken()->getTokenString(),
            'tokenType'    => 'Bearer',
        ];
    }
    
    public function resolveRefreshTokenGet( mixed $value, $args, $context, ResolveInfo $info ): array {
        return [
            'accessToken'  => $this->getSecurityToken()->getTokenString(),
            'expires'      => $this->getSecurityToken()->expiresAt(),
            'tokenType'    => 'Bearer',
        ];
    }
    
}