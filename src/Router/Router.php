<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router;

use Exception;
use Swift\DependencyInjection\Attributes\DI;
use Swift\HttpFoundation\Exception\NotFoundException;
use Swift\HttpFoundation\RequestInterface;
use Swift\Events\EventDispatcher;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Router\Event\OnBeforeRoutesCompileEvent;
use RuntimeException;
use Swift\Router\MatchTypes\MatchTypeInterface;

/**
 * Class Router
 * @package Swift\Router
 */
#[Autowire, DI( aliases: [ RouterInterface::class . '  $router' ] )]
class Router implements RouterInterface {
    
    private bool $isCompiled = false;
    
    /** @var RouteInterface[] $routeCollection */
    protected array $routeCollection = [];
    
    /**
     * @var RoutesBag Array of all routes (incl. named routes).
     */
    protected RoutesBag $routes;
    
    /**
     * @var array Array of all named routes.
     */
    protected array $namedRoutes = [];
    
    /**
     * @var string Can be used to ignore leading part of the Request URL (if main file lives in subdirectory of host)
     */
    protected string $basePath = '';
    
    /**
     * @var MatchTypeInterface[] Array of default match types (regex helpers)
     */
    protected array $matchTypes = [];
    
    /** @var RouteInterface|null $currentRoute */
    protected RouteInterface|null $currentRoute = null;
    
    /**
     * Router constructor.
     *
     * @param Collector        $collector
     * @param RequestInterface $request
     * @param EventDispatcher  $dispatcher
     */
    public function __construct(
        private readonly Collector        $collector,
        private readonly RequestInterface $request,
        private readonly EventDispatcher  $dispatcher,
    ) {
        $this->routes = new RoutesBag();
    }
    
    /**
     * Bind harvested routes to object
     *
     * @throws Exception
     */
    private function bindRoutes(): void {
        if ( empty( $this->routeCollection ) ) {
            return;
        }
        
        foreach ( $this->routeCollection as $route ) {
            $route->setMatchTypes( $this->matchTypes );
            $route->setRequest( $this->request );
            $this->addRoute( $route );
        }
    }
    
    /**
     * Get route from current url
     *
     * @return RouteInterface
     * @throws Exception
     */
    public function getCurrentRoute(): RouteInterface {
        if ( isset( $this->currentRoute ) ) {
            return $this->currentRoute;
        }
        
        if ( ! $this->isCompiled ) {
            $this->compile();
        }
        
        $this->currentRoute = $this->match( $this->request );
        
        if ( is_null( $this->currentRoute ) ) {
            throw new NotFoundException( 'Not found' );
        }
        
        return $this->currentRoute;
    }
    
    /**
     * Match a given Request Url against stored routes
     *
     * @param RequestInterface $request
     *
     * @return RouteInterface|null Matched Route object with information on success, false on failure (no match).
     */
    public function match( RequestInterface $request ): ?RouteInterface {
        foreach ( $this->routes as $handler ) {
            if ( $handler->matchesRequest( $request ) ) {
                return $handler;
            }
        }
        
        return null;
    }
    
    /**
     * Compile routes
     *
     * @throws Exception
     */
    private function compile(): void {
        $this->routeCollection = $this->collector->harvestRoutes();
        
        /** @var OnBeforeRoutesCompileEvent $onBeforeCompileRoutes */
        $onBeforeCompileRoutes = $this->dispatcher->dispatch( new OnBeforeRoutesCompileEvent( $this->routeCollection, $this->matchTypes ) );
        
        /**
         * Reassign possibly changed routes and match types
         */
        $this->routeCollection = $onBeforeCompileRoutes->getRoutes();
        $this->matchTypes      = $onBeforeCompileRoutes->getMatchTypes();
        
        $this->bindRoutes();
        $this->isCompiled = true;
    }
    
    /**
     * Retrieve array of all available routes
     *
     * @return RoutesBag
     */
    public function getRoutes(): RoutesBag {
        if ( ! $this->isCompiled ) {
            $this->compile();
        }
        
        return $this->routes;
    }
    
    /**
     * Add multiple routes at once
     *
     * @param array $routes
     */
    public function addRoutes( array $routes ): void {
        foreach ( $routes as $route ) {
            $this->addRoute( $route );
        }
    }
    
    /**
     * Add a route
     *
     * @param RouteInterface $route
     */
    public function addRoute( RouteInterface $route ): void {
        $this->routes->set( $route->getName(), $route );
        
        if ( $route->getName() ) {
            if ( isset( $this->namedRoutes[ $route->getName() ] ) ) {
                throw new RuntimeException( "Can not redeclare route '{$route->getName()}'" );
            }
            $this->namedRoutes[ $route->getName() ] = $route;
        }
    }
    
    /**
     * Set the base path.
     * Useful if you are running your application from a subdirectory.
     *
     * @param string $basePath
     */
    public function setBasePath( string $basePath ): void {
        $this->basePath = $basePath;
    }
    
    /**
     * @return string
     */
    public function getBasePath(): string {
        return $this->basePath;
    }
    
    /**
     * Add named match types. It uses array_merge so keys can be overwritten.
     *
     * @param array $matchTypes The key is the name and the value is the regex.
     */
    public function addMatchTypes( array $matchTypes ): void {
        $this->matchTypes = array_merge( $this->matchTypes, $matchTypes );
    }
    
    /**
     * @param array $matchTypes
     */
    public function setMatchTypes( array $matchTypes ): void {
        $this->matchTypes = $matchTypes;
    }
    
    /**
     * @param string $routeName The name of the route.
     * @param array  $params    @params Associative array of parameters to replace placeholders with.
     *
     * @return Route The Route object. If params are provided it will include the route with named parameters in place.
     *
     * @TODO: Make this method reverse route based on route name and params
     */
    public function generate( string $routeName, array $params = [] ): Route {
        // Check if named route exists
        if ( ! isset( $this->namedRoutes[ $routeName ] ) ) {
            throw new RuntimeException( "Route '{$routeName}' does not exist." );
        }
        
        // Replace named parameters
        $route = $this->namedRoutes[ $routeName ];
        
        // prepend base path to route url again
        $url = $this->basePath . $route;
        
        if ( preg_match_all( '`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $index => $match ) {
                [ $block, $pre, $type, $param, $optional ] = $match;
                
                if ( $pre ) {
                    $block = substr( $block, 1 );
                }
                
                if ( isset( $params[ $param ] ) ) {
                    // Part is found, replace for param value
                    $url = str_replace( $block, $params[ $param ], $url );
                } else if ( $optional && $index !== 0 ) {
                    // Only strip preceding slash if it's not at the base
                    $url = str_replace( $pre . $block, '', $url );
                } else {
                    // Strip match block
                    $url = str_replace( $block, '', $url );
                }
            }
        }
        
        return $url;
    }
    
    /**
     * Compile the regex for a given route (EXPENSIVE)
     *
     * @param string $route
     *
     * @return string
     */
    public function compileRoute( string $route ): string {
        if ( preg_match_all( '`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER ) ) {
            $matchTypes = $this->matchTypes;
            foreach ( $matches as $match ) {
                [ $block, $pre, $type, $param, $optional ] = $match;
                
                if ( isset( $matchTypes[ $type ] ) ) {
                    $type = $matchTypes[ $type ];
                }
                if ( $pre === '.' ) {
                    $pre = '\.';
                }
                
                $optional = $optional !== '' ? '?' : null;
                
                //Older versions of PCRE require the 'P' in (?P<named>)
                $pattern = '(?:'
                           . ( $pre !== '' ? $pre : null )
                           . '('
                           . ( $param !== '' ? "?P<$param>" : null )
                           . $type
                           . ')'
                           . $optional
                           . ')'
                           . $optional;
                
                $route = str_replace( $block, $pattern, $route );
            }
        }
        
        return "`^$route$`u";
    }
    
    /**
     * @inheritDoc
     */
    public function getTaggedRoutes( string $tag ): RoutesBag {
        if ( ! $this->isCompiled ) {
            $this->compile();
        }
        
        $tagged = new RoutesBag();
        foreach ( $this->routes as /** @var RouteInterface $route */ $route ) {
            if ( $route->getTags()->has( $tag ) ) {
                $tagged->set( $route->getName(), $route );
            }
        }
        
        return $tagged;
    }
    
    /**
     * @inheritDoc
     */
    public function getRoute( string $name ): ?RouteInterface {
        if ( ! $this->isCompiled ) {
            $this->compile();
        }
        
        return $this->routes->get( $name );
    }
    
    /**
     * Setter injection for match types
     *
     * @param iterable $matchTypes
     */
    #[Autowire]
    public function populateMatchTypes( #[Autowire( tag: DiTags::MATCH_TYPES )] iterable $matchTypes ): void {
        $matchTypes = iterator_to_array( $matchTypes );
        
        foreach ( $matchTypes as /** @var MatchTypeInterface */ $matchType ) {
            $this->matchTypes[ $matchType->getIdentifier() ] = $matchType;
        }
    }
}