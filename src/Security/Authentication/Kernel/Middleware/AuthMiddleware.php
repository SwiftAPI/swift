<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Kernel\Middleware;


use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Kernel\Middleware\KernelMiddlewarePriorities;
use Swift\Kernel\Middleware\MiddlewareInterface;
use Swift\Security\Authentication\AuthenticationManager;

#[Autowire]
class AuthMiddleware implements MiddlewareInterface {
    
    public function __construct(
        protected AuthenticationManager $authenticationManager,
    ) {
    }
    
    public function getPriority(): int {
        return KernelMiddlewarePriorities::AUTHENTICATION;
    }
    
    public function process(
        \Psr\Http\Message\ServerRequestInterface $request,
        \Psr\Http\Server\RequestHandlerInterface $handler,
    ): \Psr\Http\Message\ResponseInterface {
        $request = $request->withAttribute( 'auth', $this->authenticationManager->authenticate( $request ) );
        
        return $handler->handle( $request );
    }
    
}