<?php declare(strict_types=1);


namespace Swift\Router\Event;

use Swift\Kernel\Attributes\DI;
use Swift\Router\Route;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class OnBeforeRoutesCompileEvent
 * @package Swift\Router\Event
 */
#[DI(exclude: true)]
class OnBeforeRoutesCompileEvent extends Event {

    /**
     * OnBeforeRoutesCompile constructor.
     *
     * @param array $routes
     * @param array $matchTypes
     */
    public function __construct(
        private array $routes,
        private array $matchTypes,
    ) {
    }

    /**
     * @param Route $route
     */
    public function addRoute(Route $route): void {
        $this->routes[] = $route;
    }

    /**
     * @return array
     */
    public function getRoutes(): array {
        return $this->routes;
    }

    /**
     * @param array $routes
     */
    public function setRoutes( array $routes ): void {
        $this->routes = $routes;
    }

    /**
     * @param string $identifier
     * @param string $regex
     */
    public function addMatchType(string $identifier, string $regex): void {
        if (array_key_exists($identifier, $this->matchTypes)) {
            throw new \InvalidArgumentException(sprintf('Match type %s is already declared', $identifier));
        }

        $this->matchTypes[$identifier] = $regex;
    }

    /**
     * @return array
     */
    public function getMatchTypes(): array {
        return $this->matchTypes;
    }

    /**
     * @param array $matchTypes
     */
    public function setMatchTypes( array $matchTypes ): void {
        $this->matchTypes = $matchTypes;
    }

}