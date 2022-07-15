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
use Swift\DependencyInjection\Attributes\DI;
use Swift\GraphQl\DependencyInjection\DiTags;

#[DI( tags: [ DiTags::GRAPHQL_RESOLVER_MIDDLEWARE ] )]
interface ResolverMiddlewareInterface {
    
    public function process( mixed $objectValue, mixed $args, mixed $context, ResolveInfo $info, ?callable $next = null ): mixed;
    
}