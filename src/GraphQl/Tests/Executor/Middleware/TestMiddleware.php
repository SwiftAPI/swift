<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Tests\Executor\Middleware;


use GraphQL\Type\Definition\ResolveInfo;

class TestMiddleware implements \Swift\GraphQl\Executor\Middleware\ResolverMiddlewareInterface {
    
    public function process( mixed $objectValue, mixed $args, mixed $context, ResolveInfo $info, ?callable $next = null ): mixed {
        $val = $next( $objectValue, $args, $context, $info );
        
        
        return $val;
    }
    
}