<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router;

use ReflectionException;
use Swift\AuthenticationDeprecated\Types\AuthenticationLevelsEnum;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Container\Provider\ControllersAwareTrait;
use Swift\Router\Attributes\Route as RouteAttribute;

/**
 * Class Harvester
 * @package Swift\Router
 */
#[Autowire]
class Harvester {

    use ControllersAwareTrait;

    /**
     * Method to harvest routes from annotations
     *
     * @return array
     */
    public function harvestRoutes(): array {
        $harvest     = array();
        $controllers = $this->controllers;

        if ( empty( $controllers ) ) {
            return $harvest;
        }

        foreach ( $controllers as $controller ) {
            $controller = new \ReflectionClass($controller);

            try {
                $constructAttr = ! empty( $controller->getAttributes( RouteAttribute::class ) ) ?
                    $controller->getAttributes( RouteAttribute::class ) :
                    $controller?->getMethod( name: '__construct' )?->getAttributes( RouteAttribute::class );
            } catch ( ReflectionException ) {
                $constructAttr = null;
            }
            $construct = ! empty( $constructAttr ) ? $constructAttr[0]->getArguments() : null;

            $controllerRoute       = $construct ? $this->extractRoute( $construct, '', $controller?->getName(), '' ) : null;
            $baseRouteAuthRequired = $construct ? $controllerRoute->isAuthRequired() : false;
            $baseRouteAuthLevel    = $construct ? $controllerRoute->getAuthLevels() : array( AuthenticationLevelsEnum::NONE );
            $baseRoute             = $construct ? $controllerRoute->getRegex() : '';

            foreach ( $controller?->getMethods() as $method ) {
                if ( $method->getName() === '__construct' ) {
                    continue;
                }

                $methodAttr = $method?->getAttributes( RouteAttribute::class );
                $attribute  = ! empty( $methodAttr ) ? $methodAttr[0]->getArguments() : null;

                if ( ! $attribute ) {
                    continue;
                }

                $action = $method->name !== '__construct' ? $method->name : '';
                $route = $this->extractRoute( $attribute, $baseRoute, $controller?->getName(), $action );
                $route->setAuthRequired($baseRouteAuthRequired ? true : $route->isAuthRequired());
                $route->setAuthLevels($baseRouteAuthLevel === 'login' ? 'login' : $route->getAuthLevels());
                $route->setController($controller?->getName());
                $harvest[]           = $route;
            }
        }

        return $harvest;
    }

    /**
     * Method to extract route from method annotation
     *
     * @param array $attributes
     * @param string $baseRoute
     * @param string $controller
     * @param string $action
     *
     * @return RouteInterface
     */
    private function extractRoute( array $attributes, string $baseRoute, string $controller, string $action ): RouteInterface {
        $baseRoute = trim( $baseRoute, '/' );
        $route     = trim( $attributes['route'], '/' );

        $type         = is_array( $attributes['type'] ) ? $attributes['type'] : explode( '|', $attributes['type'] );
        $authRequired = array_key_exists( key: 'authRequired', array: $attributes ) ? $attributes['authRequired'] : false;
        $authLevels   = array_key_exists( key: 'authLevels', array: $attributes ) ? $attributes['authLevels'] : array(AuthenticationLevelsEnum::NONE);
        $name         = $attributes['name'] ?? null;

        return new Route( ...array(
            'name'           => $name,
            'regex'          => $route,
            'controllerBase' => $baseRoute,
            'methods'        => $type,
            'authRequired'   => $authRequired,
            'authLevels'     => $authLevels,
            'controller'     => $controller,
            'action'         => $action,
        ) );
    }

}