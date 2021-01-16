<?php declare(strict_types=1);


namespace Swift\Controller;


use Swift\Kernel\DiTags;
use Swift\Router\HTTPRequest;
use Swift\Router\Route;
use Swift\Kernel\Attributes\DI;

/**
 * Class AbstractController
 * @package Swift\Controller
 */
#[DI(tags: [DiTags::CONTROLLER])]
abstract class AbstractController implements ControllerInterface {

    /**
     * @var Route $route
     */
    protected Route $route;

    /**
     * @var HTTPRequest $HTTPRequest
     */
    protected HTTPRequest $HTTPRequest;

    /**
     * AbstractController constructor.
     *
     * @param HTTPRequest $HTTPRequest
     */
    public function __construct(
        HTTPRequest $HTTPRequest
    ) {
        $this->HTTPRequest = $HTTPRequest;
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

    /**
     * @return HTTPRequest
     */
    public function getHTTPRequest(): HTTPRequest {
        return $this->HTTPRequest;
    }
}