<?php declare(strict_types=1);

namespace Swift\Controller;


use Swift\Router\HTTPRequest;
use Swift\Router\Route;

interface ControllerInterface {

    public function getRoute(): Route;

    public function setRoute(Route $route): void;

    public function getHTTPRequest(): HTTPRequest;

}