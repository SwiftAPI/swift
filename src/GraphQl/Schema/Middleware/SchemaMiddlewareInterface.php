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
use Swift\DependencyInjection\Attributes\DI;
use Swift\GraphQl\DependencyInjection\DiTags;
use Swift\GraphQl\Schema\Registry;

#[DI( tags: [ DiTags::GRAPHQL_SCHEMA_MIDDLEWARE ] )]
interface SchemaMiddlewareInterface {
    
    /**
     * Called upon registration of a type to the registry.
     * Add/remove/modify fields on the type or the type itself.
     *
     * @param mixed                          $builder
     * @param \Swift\GraphQl\Schema\Registry $registry
     * @param callable                       $next
     *
     * @return mixed
     */
    public function process( mixed $builder, Registry $registry, callable $next ): mixed;
    
}