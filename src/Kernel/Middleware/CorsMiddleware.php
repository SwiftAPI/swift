<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Middleware;

use Swift\Configuration\ConfigurationInterface;
use Swift\DependencyInjection\Attributes\Autowire;

#[Autowire]
class CorsMiddleware implements MiddlewareInterface {
    
    public function __construct(
        protected ConfigurationInterface $configuration,
        protected \Swift\Kernel\Kernel   $application,
    ) {
    }
    
    public function getPriority(): int {
        return KernelMiddlewarePriorities::CORS;
    }
    
    public function process(
        \Psr\Http\Message\ServerRequestInterface $request,
        \Psr\Http\Server\RequestHandlerInterface $handler,
    ): \Psr\Http\Message\ResponseInterface {
        if ( $this->configuration->get( 'app.allow_cors', 'root' ) && $request->isPreflight() ) {
            return new \Swift\HttpFoundation\CorsResponse();
            // Legacy code, check if still needed
            //$response->sendOutput();
            //$this->application->finalize( $request, $response );
        }
        
        return $handler->handle( $request );
    }
    
}