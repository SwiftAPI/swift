<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Application;

require_once 'globals-and-includes.php';


use Psr\Http\Message\ServerRequestInterface;
use Swift\DependencyInjection\ContainerFactory;
use Swift\DependencyInjection\ContainerInterface;
use Swift\HttpFoundation\ServerRequest;
use Swift\Kernel\Autoloader;
use Swift\Kernel\Kernel;
use Swift\Kernel\KernelInterface;
use Swift\Kernel\Middleware\MiddlewareRunner;

class Application implements ApplicationInterface {
    
    public function __construct(
        protected KernelInterface  $kernel,
        protected MiddlewareRunner $middlewareRunner,
    ) {
    }
    
    public function run( ServerRequestInterface $request = null ): void {
        $request ??= new ServerRequest();
        
        $this->kernel?->run( $request, $this->middlewareRunner );
    }
    
    public static function create( ContainerInterface $container = null, MiddlewareRunner $middlewareRunner = null, KernelInterface $kernel = null ): self {
        Autoloader::initialize();
        $container        ??= ContainerFactory::createContainer();
        $middlewareRunner ??= new MiddlewareRunner( $container->getServiceInstancesByTag( 'kernel.request.middleware' ) );
        /** @var Kernel $kernel */
        $kernel ??= $container->get( Kernel::class );
        
        return new static( $kernel, $middlewareRunner );
    }
    
}