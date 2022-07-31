<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Middleware;

use Swift\Configuration\Configuration;
use Swift\Configuration\ConfigurationInterface;
use Swift\DependencyInjection\Attributes\Autowire;

#[Autowire]
class DefaultTimeZoneMiddleware implements MiddlewareInterface {
    
    public function __construct(
        protected ConfigurationInterface $configuration,
    ) {
    }
    
    public function getOrder(): int {
        return KernelMiddlewareOrder::TIMEZONE;
    }
    
    public function process(
        \Psr\Http\Message\ServerRequestInterface $request,
        \Psr\Http\Server\RequestHandlerInterface $handler,
    ): \Psr\Http\Message\ResponseInterface {
        date_default_timezone_set( $this->configuration->get( 'app.timezone', 'root' ) ?? 'Europe/Amsterdam' );
        
        return $handler->handle( $request );
    }
    
}