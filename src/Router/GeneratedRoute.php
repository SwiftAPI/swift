<?php declare( strict_types=1 );


namespace Swift\Router;

class GeneratedRoute {
    
    public function __construct(
        protected readonly string $path,
        protected readonly \Swift\Router\RouteInterface $route,
    ) {
    }
    
    /**
     * @return string
     */
    public function getPath(): string {
        return $this->path;
    }
    
    /**
     * @return \Swift\Router\RouteInterface
     */
    public function getRoute(): RouteInterface {
        return $this->route;
    }
    
    
    
}