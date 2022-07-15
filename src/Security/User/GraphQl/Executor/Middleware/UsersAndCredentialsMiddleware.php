<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\GraphQl\Executor\Middleware;


use GraphQL\Type\Definition\ResolveInfo;
use Swift\DependencyInjection\Attributes\Autowire;

#[Autowire]
class UsersAndCredentialsMiddleware implements \Swift\GraphQl\Executor\Middleware\ResolverMiddlewareInterface {
    
    public function __construct(
        protected \Swift\Security\User\GraphQl\Executor\UserResolver $userResolver,
        protected \Swift\Security\User\GraphQl\Executor\ClientResolver $clientResolver,
    ) {
    }
    
    public function process( mixed $objectValue, mixed $args, mixed $context, ResolveInfo $info, ?callable $next = null ): mixed {
        $val       = $next( $objectValue, $args, $context, $info );
        $queryName = $info->fieldDefinition->name;
        $name      = $this->resolveType( $info->fieldDefinition );
        
        if ( $queryName !== $name ) {
            $val = $this->doProcess( $queryName, $val, $args, $context, $info );
        }
        if ( $name ) {
            $val = $this->doProcess( $name, $val, $args, $context, $info );
        }
        
        return $val;
    }
    
    public function doProcess( string $name, mixed $objectValue, mixed $args, mixed $context, ResolveInfo $info ): mixed {
        return match ( $name ) {
            'SecurityUser' => $this->userResolver->resolveUser( $objectValue, $args, $context, $info ),
            'SecurityUsers' => $this->userResolver->resolveUsers( $objectValue, $args, $context, $info ),
            'SecurityUsersCredential' => $this->userResolver->resolveUserCredential( $objectValue, $args, $context, $info ),
            'SecurityUsersCredentials' => $this->userResolver->resolveUserCredentials( $objectValue, $args, $context, $info ),
            'SecurityClient' => $this->clientResolver->resolveClient( $objectValue, $args, $context, $info ),
            'SecurityClients' => $this->clientResolver->resolveClients( $objectValue, $args, $context, $info ),
            default => $objectValue,
        };
    }
    
    protected function resolveType( mixed $fieldDefinition ): mixed {
        if ( ! method_exists( $fieldDefinition, 'getType' ) ) {
            return $fieldDefinition->name;
        }
        
        $type = $fieldDefinition->getType();
        
        if ( method_exists( $type, 'getOfType' ) ) {
            return $this->resolveType( $type->getOfType() );
        }
        
        return $type->name;
    }
    
}