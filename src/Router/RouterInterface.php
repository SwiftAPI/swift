<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router;

use Swift\HttpFoundation\RequestInterface;
use Swift\Router\Exceptions\NotFoundException;

interface RouterInterface {

    /**
     * Retrieve current active route
     *
     * @return RouteInterface|null
     * @throws NotFoundException
     */
    public function getCurrentRoute(): ?RouteInterface;

    /**
     * Retrieve array of all available routes
     *
     * @return RoutesBag
     */
    public function getRoutes(): RoutesBag;

    /**
     * Add multiple routes at once
     *
     * @param RouteInterface[] $routes
     */
    public function addRoutes(array $routes): void;

    /**
     * Add a route
     *
     * @param RouteInterface $route
     */
    public function addRoute(RouteInterface $route): void;

    /**
     * Set the base path.
     * Useful if you are running your application from a subdirectory.
     *
     * @param string $basePath
     */
    public function setBasePath(string $basePath): void;

    /**
     * Add named match types. It uses array_merge so keys can be overwritten.
     *
     * @param array $matchTypes The key is the name and the value is the regex.
     */
    public function addMatchTypes(array $matchTypes): void;

    /**
     * @param string $routeName The name of the route.
     * @param array $params @params Associative array of parameters to replace placeholders with.
     *
     * @return Route The Route object. If params are provided it will include the route with named parameters in place.
     */
    public function generate(string $routeName, array $params = array()): Route;

    /**
     * Match a given Request Url against stored routes
     *
     * @param RequestInterface $request
     *
     * @return RouteInterface|null Matched Route object with information on success, false on failure (no match).
     */
    public function match(RequestInterface $request): ?RouteInterface;

    /**
     * Compile the regex for a given route (EXPENSIVE)
     *
     * @param string $route
     *
     * @return string
     */
    public function compileRoute(string $route): string;

    /**
     * Get all routes containing provided tag
     *
     * @param string $tag
     *
     * @return RoutesBag
     */
    public function getTaggedRoutes( string $tag ): RoutesBag;

    /**
     * Get route by name
     *
     * @param string $name
     *
     * @return RouteInterface|null
     */
    public function getRoute( string $name ): ?RouteInterface;

}