<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Firewall\Kernel\Middleware;

use Swift\Kernel\Middleware\KernelMiddlewareOrder;
use Swift\Kernel\Middleware\MiddlewareInterface;

class RateLimitingMiddleware implements MiddlewareInterface {
    
    public function getOrder(): int {
        return KernelMiddlewareOrder::RATE_LIMIT;
    }
    
    public function process(
        \Psr\Http\Message\ServerRequestInterface $request,
        \Psr\Http\Server\RequestHandlerInterface $handler,
    ): \Psr\Http\Message\ResponseInterface {
        
        
        return $handler->handle( $request );
    }
    
}