<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Event;

use Psr\Http\Message\RequestInterface;
use Swift\DependencyInjection\Attributes\DI;
use Swift\Events\AbstractEvent;
use Swift\Router\RouteInterface;

/**
 * Class OnKernelRouteEvent
 * @package Swift\Kernel\Event
 */
#[DI(autowire: false)]
class OnKernelRouteEvent extends AbstractEvent {

    protected static string $eventDescription = 'Route has matched and authentication is completed';
    protected static string $eventLongDescription = '';

    /**
     * KernelRequest constructor.
     *
     * @param RequestInterface $request
     * @param RouteInterface $route
     */
    public function __construct(
        private readonly RequestInterface $request,
        private RouteInterface            $route,
    ) {
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface {
        return $this->request;
    }

    /**
     * @return RouteInterface
     */
    public function getRoute(): RouteInterface {
        return $this->route;
    }

    /**
     * @param RouteInterface $route
     */
    public function setRoute( RouteInterface $route ): void {
        $this->route = $route;
    }


}