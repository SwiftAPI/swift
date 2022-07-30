<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Firewall\GraphQl\Executor\Middleware;


use GraphQL\Type\Definition\ResolveInfo;
use Swift\Code\PropertyReader;
use Swift\Configuration\ConfigurationInterface;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\GraphQl\Exception\FieldUnAuthorizedException;
use Swift\Security\Authorization\AuthorizationCheckerInterface;

/**
 * Validate GraphQL requests against the current user.
 * Determine if the current user is authorized to access the requested field.
 * If not, throw an exception.
 *
 * First check field definition grant, after that check based on security configuration.
 */
#[Autowire]
class AuthMiddleware implements \Swift\GraphQl\Executor\Middleware\ResolverMiddlewareInterface {
    
    protected readonly array $configs;
    
    public function __construct(
        protected AuthorizationCheckerInterface $authorizationChecker,
        protected ConfigurationInterface        $configuration,
        protected PropertyReader                $propertyReader,
    ) {
        $this->initConfigs();
    }
    
    public function process( mixed $objectValue, mixed $args, mixed $context, ResolveInfo $info, ?callable $next = null ): mixed {
        $val       = $next( $objectValue, $args, $context, $info );
        $queryName = $info->fieldDefinition->name;
        $name      = $this->resolveType( $info->fieldDefinition );
        
        if ( ! empty( $info->fieldDefinition->config[ 'auth' ][ 'isGranted' ] ) && ! $this->authorizationChecker->isGranted( $info->fieldDefinition->config[ 'auth' ][ 'isGranted' ] ?? [], $info ) ) {
            throw FieldUnAuthorizedException::queryOrMutationUnAuthorized( $name );
        }
        
        if ( $queryName !== $name ) {
            $val = $this->checkField( $queryName, $val, $args, $context, $info );
        }
        
        return $this->checkField( $name, $val, $args, $context, $info );
    }
    
    protected function checkField( ?string $name, mixed $objectValue, mixed $args, mixed $context, ResolveInfo $info ): mixed {
        if ( ! $name ) {
            return $objectValue;
        }
        
        if ( array_key_exists( $name, $this->configs ) ) {
            if ( ! empty( $this->configs[ $name ][ 'config' ][ 'roles' ] ) && ! $this->authorizationChecker->isGranted( $this->configs[ $name ][ 'config' ][ 'roles' ], $info ) ) {
                throw FieldUnAuthorizedException::queryOrMutationUnAuthorized( $name );
            }
            
            $fieldSelection = $info->getFieldSelection();
            foreach ( $this->configs[ $name ][ 'fields' ] ?? [] as $key => $field ) {
                if ( ! ( $fieldSelection[ $key ] ?? null ) ) {
                    continue;
                }
                
                
                if ( ! $this->authorizationChecker->isGranted( $field[ 'config' ][ 'roles' ] ?? [], $info ) ) {
                    $info->fieldDefinition->getType()->getFields()[ $key ]->resolveFn = static fn() => throw FieldUnAuthorizedException::fieldUnAuthorized( $key, $name );
                }
            }
        }
        
        return $objectValue;
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
    
    protected function initConfigs(): void {
        $configs = [];
        
        foreach ( $this->configuration->get( 'graphql_access_control', 'security' ) as $config ) {
            [ 'name' => $name, 'fields' => $fields, 'roles' => $roles, 'ip' => $ip ] = $config;
            if ( isset( $configs[ $name ] ) ) {
                $configs[ $name ] = [
                    'config' => [],
                    'fields' => [],
                ];
            }
            if ( empty( $fields ) ) {
                $configs[ $name ][ 'config' ] = $config;
            } else {
                foreach ( $fields as $field ) {
                    $configs[ $name ][ 'fields' ][ $field ] = [
                        'config' => $config,
                    ];
                }
            }
        }
        
        $this->configs = $configs;
    }
    
}