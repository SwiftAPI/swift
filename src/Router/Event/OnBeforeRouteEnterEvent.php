<?php declare(strict_types=1);
/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router\Event;

use Swift\Events\AbstractEvent;
use Swift\Kernel\Attributes\DI;
use Swift\Router\Route;

/**
 * Class OnBeforeRouteEnterEvent
 * @package Swift\Router\Event
 */
#[DI(autowire: false)]
class OnBeforeRouteEnterEvent extends AbstractEvent {

    protected static string $eventDescription = 'Route is matched, but not the Controller has not been called yet';
    protected static string $eventLongDescription = '';

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