<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\WebSocket\Router;

use Swift\Code\ReflectionFactory;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Router\RoutesBag;
use Swift\Runtime\RuntimeDiTags;
use Swift\WebSocket\Attributes\SocketRoute;

#[Autowire]
class Collector {
    
    /**
     * @var \Swift\WebSocket\Controller\WebSocketControllerInterface[] $sockets
     */
    protected array $sockets = [];
    
    /**
     * @param \Swift\Code\ReflectionFactory $reflectionFactory
     */
    public function __construct(
        protected ReflectionFactory $reflectionFactory,
    ) {
    }
    
    /**
     * @return \Swift\Router\RoutesBag
     */
    public function getRoutes(): RoutesBag {
        $routes = new RoutesBag();
        
        foreach ( $this->sockets as $socket ) {
            $reflection = $this->reflectionFactory->getReflectionClass( $socket );
            /** @var SocketRoute $socketRouteAttribute */
            $socketRouteAttribute = $this->reflectionFactory->getAttributeReader()->getClassAnnotation( $reflection, SocketRoute::class );
            
            $routes->set(
                $socketRouteAttribute->getName(),
                new Route(
                    $socketRouteAttribute->getName(),
                    $socketRouteAttribute->getRoute(),
                    $socket::class,
                    $socketRouteAttribute->getAuthTypes(),
                    $socketRouteAttribute->getIsGranted(),
                    [],
                ) ,
            );
        }
        
        return $routes;
    }
    
    
    /**
     * @param \Swift\WebSocket\Controller\WebSocketControllerInterface[] $sockets
     */
    #[Autowire]
    public function setSockets( #[Autowire( tag: RuntimeDiTags::SOCKET_CONTROLLER )] ?iterable $sockets ): void {
        if ( ! $sockets ) {
            return;
        }
        
        $this->sockets = iterator_to_array( $sockets );
    }
    
}