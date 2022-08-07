<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl;


use Swift\HttpFoundation\RequestMatcher;
use Swift\Router\Types\RouteMethod;

class Util {
    
    public static function isGraphQlRequest( \Psr\Http\Message\ServerRequestInterface $request ): bool {
        $matcher = new RequestMatcher( '/graphql/', null, [ RouteMethod::POST, RouteMethod::GET ] );
        
        return $matcher->matches( $request );
    }
    
}