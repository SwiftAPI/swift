<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\RateLimiter\Kernel\Middleware;

use Swift\Configuration\ConfigurationInterface;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Kernel\Middleware\KernelMiddlewareOrder;
use Swift\Kernel\Middleware\MiddlewareInterface;
use Swift\Security\RateLimiter\RateLimit;
use Swift\Security\RateLimiter\RateLimiterFactory;

#[Autowire]
class RateLimitingMiddleware implements MiddlewareInterface {
    
    public function __construct(
        protected RateLimiterFactory     $rateLimiterFactory,
        protected ConfigurationInterface $configuration,
    ) {
    }
    
    public function getOrder(): int {
        return KernelMiddlewareOrder::RATE_LIMIT;
    }
    
    public function process(
        \Psr\Http\Message\ServerRequestInterface $request,
        \Psr\Http\Server\RequestHandlerInterface $handler,
    ): \Psr\Http\Message\ResponseInterface {
        // If rate limiting is disabled, skip this middleware
        if ( ! $this->configuration->get( 'rate_limit.enabled', 'security' ) ) {
            return $handler->handle( $request );
        }
        // If this is a GraphQL request, skip this middleware. GraphQL rate limiting is handled by the QueryComplexityRateLimiter rule.
        if ( \Swift\GraphQl\Util::isGraphQlRequest( $request ) ) {
            return $handler->handle( $request );
        }
        
        if ( $this->configuration->get('rate_limit.enable_default', 'security') ) {
            $limiter = $this->rateLimiterFactory->create( 'default', \Swift\Security\RateLimiter\Util::getStateFromRequest( $request ) );
            $rate = $limiter?->consume( 1 );
    
            $rate->denyIfNotAccepted();
        }
        
        $response = $handler->handle( $request );
        
        return RateLimit::bindToResponse( $rate ?? null, $response );
    }
    
}