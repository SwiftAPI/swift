<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router\Event;

use Swift\Kernel\Attributes\DI;
use Swift\Router\MatchTypes\MatchTypeInterface;
use Swift\Router\Route;
use Swift\Router\RouteInterface;
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
     * @param RouteInterface[] $routes
     * @param MatchTypeInterface[] $matchTypes
     */
    public function __construct(
        private array $routes,
        private array $matchTypes,
    ) {
    }

    /**
     * @param RouteInterface $route
     */
    public function addRoute(RouteInterface $route): void {
        $this->routes[] = $route;
    }

    /**
     * @return RouteInterface[]
     */
    public function getRoutes(): array {
        return $this->routes;
    }

    /**
     * @param RouteInterface[] $routes
     */
    public function setRoutes( array $routes ): void {
        $this->routes = $routes;
    }

    /**
     * @param MatchTypeInterface $matchType
     */
    public function addMatchType(MatchTypeInterface $matchType): void {
        if (array_key_exists($matchType->getIdentifier(), $this->matchTypes)) {
            throw new \InvalidArgumentException(sprintf('Match type %s is already declared', $matchType->getIdentifier()));
        }

        $this->matchTypes[$matchType->getIdentifier()] = $matchType;
    }

    /**
     * @return MatchTypeInterface[]
     */
    public function getMatchTypes(): array {
        return $this->matchTypes;
    }

    /**
     * @param MatchTypeInterface[] $matchTypes
     */
    public function setMatchTypes( array $matchTypes ): void {
        $this->matchTypes = $matchTypes;
    }

}