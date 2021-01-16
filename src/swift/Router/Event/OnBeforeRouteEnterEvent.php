<?php declare(strict_types=1);

namespace Swift\Router\Event;

use Swift\Kernel\Attributes\DI;
use Swift\Router\Route;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class OnBeforeRouteEnterEvent
 * @package Swift\Router\Event
 */
#[DI(exclude: true)]
class OnBeforeRouteEnterEvent extends Event {

    /**
     * OnBeforeRouteEnter constructor.
     *
     * @param $route
     */
    public function __construct(
        private Route $route,
    ) {
    }

    /**
     * @return Route
     */
    public function getRoute(): Route {
        return $this->route;
    }

    /**
     * @param Route $route
     */
    public function setRoute( Route $route ): void {
        $this->route = $route;
    }
}