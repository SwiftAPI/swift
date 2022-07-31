<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swift\Configuration\ConfigurationInterface;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Kernel\Deprecations\Deprecation;
use Swift\Kernel\Deprecations\DeprecationLevel;

#[Autowire]
class DeprecationMiddleware implements MiddlewareInterface {
    
    public function __construct(
        protected ConfigurationInterface $configuration,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public function getOrder(): int {
        return KernelMiddlewareOrder::DEPRECATION;
    }
    
    /**
     * @inheritDoc
     */
    public function process( ServerRequestInterface $request, RequestHandlerInterface $handler ): ResponseInterface {
        Deprecation::setDeprecationLevel(
            $this->configuration->get( 'app.debug', 'app' ) ?
                DeprecationLevel::TRIGGER_ERROR : DeprecationLevel::NONE
        );
        
        return $handler->handle( $request );
    }
}