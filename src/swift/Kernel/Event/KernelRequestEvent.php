<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Event;

use Swift\HttpFoundation\RequestInterface;
use Swift\Kernel\Attributes\DI;
use Swift\Router\RouteInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class KernelRequestEvent
 * @package Swift\Kernel\Event
 */
#[DI(autowire: false)]
class KernelRequestEvent extends Event {

    /**
     * KernelRequest constructor.
     *
     * @param RequestInterface $request
     * @param RouteInterface $route
     */
    public function __construct(
        private RequestInterface $request,
        private RouteInterface $route,
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