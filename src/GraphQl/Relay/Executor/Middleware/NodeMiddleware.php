<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Relay\Executor\Middleware;


use GraphQL\Type\Definition\ResolveInfo;
use Swift\Code\PropertyReader;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\GraphQl\Relay\Relay;
use Swift\GraphQl\Schema\Registry;

#[Autowire]
class NodeMiddleware implements \Swift\GraphQl\Executor\Middleware\ResolverMiddlewareInterface {
    
    public function __construct(
        protected PropertyReader $propertyReader,
    ) {
    }
    
    public function process( mixed $objectValue, mixed $args, mixed $context, ResolveInfo $info, ?callable $next = null ): mixed {
        if ( ! ( $info->fieldDefinition->getType() instanceof \GraphQL\Type\Definition\ObjectType ) ||
             ! $info->fieldDefinition->getType()->implementsInterface( Registry::$typeMap[ Relay::NODE ] )
        ) {
            return $next( $objectValue, $args, $context, $info );
        }
        
        if ( ! empty( $args[ 'id' ] ) ) {
            $response     = Relay::decodeId( $args[ 'id' ] );
            $args[ 'id' ] = (int) $response[ 'id' ];
        }
        
        return $next( $objectValue, $args, $context, $info );
    }
    
}