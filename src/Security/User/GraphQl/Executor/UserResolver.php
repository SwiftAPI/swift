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
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\GraphQl\Exception\FieldUnAuthorizedException;
use Swift\HttpFoundation\Response;
use Swift\Orm\EntityManagerInterface;
use Swift\Security\Authorization\AuthorizationRole;
use Swift\Security\User\Entity\UserEntity;

#[Autowire]
class UserResolver extends \Swift\GraphQl\Executor\Resolver\AbstractResolver {
    
    public function __construct(
        protected EntityManagerInterface $entityManager,
    ) {
    }
    
    public function resolveCurrentUser( mixed $value, $args, $context, ResolveInfo $info ): \Swift\Security\User\Entity\UserEntity {
        $this->denyAccessUnlessGranted( [ AuthorizationRole::ROLE_USER, AuthorizationRole::ROLE_CLIENT ] );
        
        return $this->entityManager->findByPk( UserEntity::class, $this->getCurrentUser()->getId() );
    }
    
    public function resolveLogin( mixed $value, $args, $context, ResolveInfo $info ): array {
        $user = $this->entityManager->findByPk( UserEntity::class, $this->getCurrentUser()->getId() );
        
        return [
            'user'  => $user,
            'token' => [
                'token'   => $this->getSecurityToken()->getTokenString(),
                'expires' => $this->getSecurityToken()->expiresAt(),
            ],
        ];
    }
    
    public function resolveForgotPassword( mixed $value, $args, $context, ResolveInfo $info ): array {
        $this->denyAccessUnlessGranted( [ AuthorizationRole::ROLE_GUEST ] );
   
        return [
            'message' => 'Successfully requested reset password token. The user has been notified.',
            'code'    => Response::HTTP_OK,
        ];
    }
    
    public function resolveResetPassword( mixed $value, $args, $context, ResolveInfo $info ): array {
        $this->denyAccessUnlessGranted( [ AuthorizationRole::ROLE_GUEST ] );
        
        return [
            'message' => 'Successfully reset password.',
            'code'    => Response::HTTP_OK,
        ];
    }
    
    public function resolveUserCredential( \Swift\Security\User\Entity\UserCredentials $value, $args, $context, ResolveInfo $info ): \Swift\Security\User\Entity\UserCredentials {
        if ( $this->getCurrentUser()->getUuid() === $value->user->getUuid() ) {
            return $value;
        }
        
        if ( ! $this->authorizationChecker->isGranted( [ AuthorizationRole::ROLE_ADMIN ] ) ) {
            throw FieldUnAuthorizedException::fieldUnAuthorized( $info->fieldName, $info->parentType->name );
        }
        
        return $value;
    }
    
    public function resolveUserCredentials( mixed $value, $args, $context, ResolveInfo $info ): mixed {
        if ( ! $this->authorizationChecker->isGranted( [ AuthorizationRole::ROLE_ADMIN ] ) ) {
            throw FieldUnAuthorizedException::fieldUnAuthorized( $info->fieldName, $info->parentType->name );
        }
        
        return $value;
    }
    
    public function resolveUser( \Swift\Security\User\Entity\UserEntity $value, $args, $context, ResolveInfo $info ): \Swift\Security\User\Entity\UserEntity {
        if ( $this->getCurrentUser()->getUuid() === $value->getUuid() ) {
            return $value;
        }
        
        if ( ! $this->authorizationChecker->isGranted( [ AuthorizationRole::ROLE_USERS_LIST ] ) ) {
            throw FieldUnAuthorizedException::fieldUnAuthorized( $info->fieldName, $info->parentType->name );
        }
        
        return $value;
    }
    
    public function resolveUsers( mixed $value, $args, $context, ResolveInfo $info ): mixed {
        if ( ! $this->authorizationChecker->isGranted( [ AuthorizationRole::ROLE_USERS_LIST ] ) ) {
            throw FieldUnAuthorizedException::fieldUnAuthorized( $info->fieldName, $info->parentType->name );
        }
        
        return $value;
    }
    
}