<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router\Kernel\Middleware;


use Psr\Http\Message\ResponseInterface;
use Swift\Configuration\ConfigurationInterface;
use Swift\Configuration\Utils;
use Swift\Controller\ControllerInterface;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\DependencyInjection\ContainerInterface;
use Swift\Events\EventDispatcherInterface;
use Swift\HttpFoundation\Exception\NotFoundException;
use Swift\Kernel\Event\OnKernelRouteEvent;
use Swift\Kernel\Middleware\KernelMiddlewareOrder;
use Swift\Kernel\Middleware\MiddlewareInterface;
use Swift\Router\Event\OnBeforeRouteEnterEvent;
use Swift\Router\Route;
use Swift\Router\RouterInterface;

#[Autowire]
class RestMiddleware implements MiddlewareInterface {
    
    protected ContainerInterface $container;
    
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected RouterInterface          $router,
        protected ConfigurationInterface   $configuration,
    ) {
    }
    
    public function getOrder(): int {
        return KernelMiddlewareOrder::REST;
    }
    
    public function process(
        \Psr\Http\Message\ServerRequestInterface $request,
        \Psr\Http\Server\RequestHandlerInterface $handler,
    ): \Psr\Http\Message\ResponseInterface {
        var_dump( $request->getAttributes() );
        
        $route = ( $this->eventDispatcher->dispatch( new OnKernelRouteEvent( $request, $this->router->getCurrentRoute() ) ) )->getRoute();
        
        
        /** @var Route $route */
        $route = ( $this->eventDispatcher->dispatch( new OnBeforeRouteEnterEvent( $route ) ) )->getRoute();
        
        if ( ! $this->container->has( $route->getController() ) ) {
            throw new NotFoundException( 'Not found' );
        }
        
        /** @var ControllerInterface $controller */
        $controller = $this->container->get( $route->getController() );
        
        if ( $controller instanceof ControllerInterface ) {
            $controller->setRoute( $route );
        }
        
        if ( empty( $route->getAction() ) || ! method_exists( $controller, $route->getAction() ) ) {
            throw new NotFoundException(
                Utils::isDebug( $this->configuration ) ? sprintf( 'Action %s not found on controller %s', $route->getAction(), $controller::class ) : 'Action not found'
            );
        }
        
        /** @var ResponseInterface $response */
        $response = $controller->{$route->getAction()}( $route->getParams() );
        
        return $response;
    }
    
    #[Autowire]
    public function setContainer( #[Autowire( serviceId: 'service_container' )] ContainerInterface $container ): void {
        $this->container = $container;
    }
    
}