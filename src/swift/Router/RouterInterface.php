<?php declare(strict_types=1);

/**
 * @copyright Alto Router
 * @see https://altorouter.com/
 */

namespace Swift\Router;

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
     * @return array
     */
    public function getRoutes(): array;

    /**
     * Add multiple routes at once
     *
     * @param array $routes
     */
    public function addRoutes(array $routes): void;

    /**
     * Add a route
     *
     * @param Route $route
     */
    public function addRoute(Route $route): void;

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
     * @param string|null $requestUrl
     * @param string|null $requestMethod
     *
     * @return RouteInterface|null Matched Route object with information on success, false on failure (no match).
     */
    public function match(string $requestUrl = null, string $requestMethod = null): ?RouteInterface;

    /**
     * Compile the regex for a given route (EXPENSIVE)
     *
     * @param string $route
     *
     * @return string
     */
    public function compileRoute(string $route): string;

}