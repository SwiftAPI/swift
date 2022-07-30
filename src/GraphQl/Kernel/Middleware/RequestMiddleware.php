<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Kernel\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swift\Configuration\ConfigurationInterface;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\GraphQl\Kernel;
use Swift\HttpFoundation\RequestMatcher;
use Swift\Kernel\Middleware\KernelMiddlewarePriorities;
use Swift\Kernel\Middleware\MiddlewareInterface;
use Swift\Router\Types\RouteMethod;

#[Autowire]
class RequestMiddleware implements MiddlewareInterface {
    
    public function __construct(
        protected Kernel                 $kernel,
        protected ConfigurationInterface $configuration,
    ) {
    }
    
    public function getPriority(): int {
        return KernelMiddlewarePriorities::GRAPHQL;
    }
    
    public function process( ServerRequestInterface $request, RequestHandlerInterface $handler ): ResponseInterface {
        if ( ! $this->configuration->get( 'graphql.enabled', 'app' ) ) {
            return $handler->handle( $request );
        }
        
        $matcher = new RequestMatcher( '/graphql/', null, [ RouteMethod::POST, RouteMethod::GET ] );
        if ( $matcher->matches( $request ) ) {
            return $this->kernel->handle( $request );
        }
        
        return $handler->handle( $request );
    }
    
}