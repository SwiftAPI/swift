<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Schema\Middleware;

use GraphQL\Type\Definition\ResolveInfo;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\GraphQl\DependencyInjection\DiTags;
use Swift\GraphQl\Schema\Registry;

#[Autowire]
class SchemaMiddlewareExecutor {
    
    /** @var \Swift\GraphQl\Schema\Middleware\SchemaMiddlewareInterface[] $middlewares */
    protected array $middlewares = [];
    
    public function process( mixed $builder, Registry $registry, ?callable $finalize = null ): mixed {
        return $this->callMiddleware( $builder, $registry, $finalize, 0 );
    }
    
    protected function callMiddleware(
        mixed    $builder,
        Registry $registry,
        callable $finalize,
        int      $key,
    ): mixed {
        $middlewares = $this->middlewares;
        $ref         = $this;
        
        if ( ! array_key_exists( $key, $middlewares ) ) {
            return $finalize( $builder, $registry );
        }
        
        return $middlewares[ $key ]->process(
            $builder,
            $registry,
            static function ( mixed $builder, $registry ) use ( $ref, $finalize, $key ) {
                return $ref->callMiddleware( $builder, $registry, $finalize, $key + 1 );
            }
        );
    }
    
    #[Autowire]
    public function setMiddlewares( #[Autowire( tag: DiTags::GRAPHQL_SCHEMA_MIDDLEWARE )] ?iterable $middlewares ): void {
        if ( ! $middlewares ) {
            return;
        }
        
        $this->middlewares = iterator_to_array( $middlewares );
    }
    
}