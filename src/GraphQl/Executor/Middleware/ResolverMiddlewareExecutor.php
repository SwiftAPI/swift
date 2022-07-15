<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Executor\Middleware;

use GraphQL\Type\Definition\ResolveInfo;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\GraphQl\DependencyInjection\DiTags;

#[Autowire]
class ResolverMiddlewareExecutor {
    
    /** @var \Swift\GraphQl\Executor\Middleware\ResolverMiddlewareInterface[] $middlewares */
    protected array $middlewares = [];
    
    public function process( mixed $objectValue, mixed $args, mixed $context, ResolveInfo $info, ?callable $finalize = null ): mixed {
        return $this->callMiddleware( $objectValue, $args, $context, $info, $finalize, 0 );
    }
    
    protected function callMiddleware(
        mixed       $objectValue,
        mixed       $args,
        mixed       $context,
        ResolveInfo $info,
        callable    $finalize,
        int         $key,
    ): mixed {
        $middlewares = $this->middlewares;
        $ref         = $this;
        
        if ( ! array_key_exists( $key, $middlewares ) ) {
            return $finalize( $objectValue, $args, $context, $info );
        }
        
        return $middlewares[ $key ]->process(
            $objectValue,
            $args,
            $context,
            $info,
            static function ( mixed $objectValue, mixed $args, mixed $context, ResolveInfo $info ) use ( $ref, $finalize, $key ) {
                return $ref->callMiddleware( $objectValue, $args, $context, $info, $finalize, $key + 1 );
            }
        );
    }
    
    #[Autowire]
    public function setMiddlewares( #[Autowire( tag: DiTags::GRAPHQL_RESOLVER_MIDDLEWARE )] ?iterable $middlewares ): void {
        if ( ! $middlewares ) {
            return;
        }
        
        $this->middlewares = iterator_to_array( $middlewares );
    }
    
}