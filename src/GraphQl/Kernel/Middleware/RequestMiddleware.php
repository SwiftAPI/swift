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
use Swift\GraphQl\Util;
use Swift\Kernel\Middleware\KernelMiddlewareOrder;
use Swift\Kernel\Middleware\MiddlewareInterface;

#[Autowire]
class RequestMiddleware implements MiddlewareInterface {
    
    public function __construct(
        protected Kernel                 $kernel,
        protected ConfigurationInterface $configuration,
    ) {
    }
    
    public function getOrder(): int {
        return KernelMiddlewareOrder::GRAPHQL;
    }
    
    public function process( ServerRequestInterface $request, RequestHandlerInterface $handler ): ResponseInterface {
        if ( ! $this->configuration->get( 'graphql.enabled', 'app' ) ) {
            return $handler->handle( $request );
        }
        
        if ( Util::isGraphQlRequest( $request ) ) {
            return $this->kernel->handle( $request );
        }
        
        return $handler->handle( $request );
    }
    
}