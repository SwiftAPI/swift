<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Controller;


use Psr\Http\Message\RequestInterface;
use Swift\HttpFoundation\ServerRequest;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\DiTags;
use Swift\Kernel\Attributes\DI;
use Swift\Router\RouteInterface;

/**
 * Class AbstractController
 * @package Swift\Controller
 */
#[DI(tags: [DiTags::CONTROLLER]), Autowire]
abstract class AbstractController implements ControllerInterface {

    protected RouteInterface $route;
    protected RequestInterface $request;

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

    /**
     * @param RequestInterface $serverRequest
     */
    #[Autowire]
    public function setRequest( #[Autowire(serviceId: ServerRequest::class)] RequestInterface $serverRequest ): void {
        var_dump('here');
        $this->request = $serverRequest;
    }

    /**
     * Get current request
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface {
        return $this->request;
    }
}