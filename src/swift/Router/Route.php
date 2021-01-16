<?php declare(strict_types=1);

namespace Swift\Router;

use JetBrains\PhpStorm\Pure;
use Swift\Kernel\Attributes\DI;

/**
 * Class Route
 * @package Swift\Router
 */
#[DI(exclude: true)]
class Route {

    /**
     * @var string|null $name
     */
    public string|null $name;

    /**
     * @var string $regex The route regex, custom regex must start with an @. You can use multiple pre-set regex filters, like [i:id]
     */
    public string $regex;

    /**
     * @var string|null $controllerBase Controller base url/regex before the route
     */
    public string|null $controllerBase = null;

    /**
     * @var array $methods HTTP methods on which the route applies. Methods must be one or more of 5 HTTP Methods (GET|POST|PATCH|PUT|DELETE)
     */
    public array $methods = array();

    /**
     * @var string $controller FQCN of the controller class
     */
    public string $controller;

    /**
     * @var string|null $action
     */
    public string|null $action;

    /**
     * @var array $params
     */
    public array $params;

    /**
     * @var bool $authRequired
     */
    public bool $authRequired;

    /**
     * @var string $authLevel
     */
    public string $authLevel;

    /**
     * Route constructor.
     *
     * @param array $route
     */
    #[Pure]
    public function __construct( array $route = array()) {
        foreach ($route as $key => $item) {
            if (property_exists($this, $key)) {
                $this->{$key} = $item;
            }
        }
    }

    /**
     * Get full route regex including controller base
     *
     * @return string|null
     */
    #[Pure]
    public function getFullRegex(): ?string {
        if (is_null($this->regex)) {
            return null;
        }

        return is_null($this->controllerBase) ? $this->regex : $this->controllerBase . '/' . $this->regex;
    }

    /**
     * Checks whether a given HTTPMethod applies for this route
     *
     * @param string $method
     *
     * @return bool
     */
    #[Pure]
    public function methodApplies( string $method): bool {
        return in_array($method, $this->methods, false);
    }

}