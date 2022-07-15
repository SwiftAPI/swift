<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router;

use ReflectionException;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\DependencyInjection\Provider\ControllersAwareTrait;
use Swift\DependencyInjection\ServiceLocator;
use Swift\Kernel\KernelDiTags;
use Swift\Router\Attributes\Route as RouteAttribute;
use Swift\Security\Authorization\AuthorizationType;

/**
 * Class Collector
 * @package Swift\Router
 */
#[Autowire]
class Collector {
    
    use ControllersAwareTrait;
    
    /**
     * Method to harvest routes from annotations
     *
     * @return RouteInterface[]
     */
    public function harvestRoutes(): array {
        $harvest     = [];
        $controllers = $this->controllers;
        
        if ( empty( $controllers ) ) {
            return $harvest;
        }
        
        foreach ( $controllers as $controller ) {
            $controller = new \ReflectionClass( $controller );
            
            try {
                $constructAttr = ! empty( $controller->getAttributes( RouteAttribute::class ) ) ?
                    $controller->getAttributes( RouteAttribute::class ) :
                    $controller?->getMethod( name: '__construct' )?->getAttributes( RouteAttribute::class );
            } catch ( ReflectionException ) {
                $constructAttr = null;
            }
            $construct = ! empty( $constructAttr ) ? $constructAttr[ 0 ]->getArguments() : null;
            
            $controllerRoute    = $construct ? $this->extractRoute( $construct, '', $controller?->getName(), '', true ) : null;
            $baseRouteIsGranted = $construct ? $controllerRoute->getIsGranted() : [];
            $baseRouteAuthLevel = $construct ? $controllerRoute->getAuthType() : [ AuthorizationType::PUBLIC_ACCESS ];
            $baseRoute          = $construct ? $controllerRoute->getRegex() : '';
            
            foreach ( $controller?->getMethods() as $method ) {
                if ( $method->getName() === '__construct' ) {
                    continue;
                }
                
                $methodAttr = $method?->getAttributes( RouteAttribute::class );
                $attribute  = ! empty( $methodAttr ) ? $methodAttr[ 0 ]->getArguments() : null;
                
                if ( ! $attribute ) {
                    continue;
                }
                
                $action = $method->name !== '__construct' ? $method->name : '';
                $route  = $this->extractRoute( $attribute, $baseRoute, $controller?->getName(), $action );
                $route->setAuthType( $baseRouteAuthLevel === 'login' ? 'login' : $route->getAuthType() );
                $route->setIsGranted( array_unique( array_merge( $baseRouteIsGranted, $route->getIsGranted() ) ) );
                $route->setController( $controller?->getName() );
                $route->setControllerRoute( $controllerRoute );
                $harvest[] = $route;
            }
        }
        
        return $harvest;
    }
    
    /**
     * Method to extract route from method annotation
     *
     * @param array  $attributes
     * @param string $baseRoute
     * @param string $controller
     * @param string $action
     * @param bool   $isController
     *
     * @return RouteInterface
     */
    private function extractRoute( array $attributes, string $baseRoute, string $controller, string $action, bool $isController = false ): RouteInterface {
        $route = trim( $attributes[ 'route' ], '/' );
        
        $attributes[ 'method' ] ??= [];
        $methods                = is_array( $attributes[ 'method' ] ) ? $attributes[ 'method' ] : [ $attributes[ 'method' ] ];
        
        $authType  = array_key_exists( key: 'authType', array: $attributes ) ? $attributes[ 'authType' ] : [ AuthorizationType::PUBLIC_ACCESS ];
        $isGranted = array_key_exists( key: 'isGranted', array: $attributes ) ? $attributes[ 'isGranted' ] : [];
        $name      = $attributes[ 'name' ] ?? null;
        $tags      = $attributes[ 'tags' ] ?? [];
        
        return $isController ?
            new ControllerRoute(
                ...[
                       'name'       => $name,
                       'regex'      => $route,
                       'methods'    => $methods,
                       'isGranted'  => $isGranted,
                       'authType'   => $authType,
                       'controller' => $controller,
                       'action'     => $action,
                       'tags'       => $tags,
                   ]
            ) :
            new Route(
                ...[
                       'name'       => $name,
                       'regex'      => $route,
                       'methods'    => $methods,
                       'isGranted'  => $isGranted,
                       'authType'   => $authType,
                       'controller' => $controller,
                       'action'     => $action,
                       'tags'       => $tags,
                   ]
            );
    }
    
}