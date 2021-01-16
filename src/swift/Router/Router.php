<?php declare(strict_types=1);

namespace Swift\Router;


use Exception;
use Swift\Configuration\Configuration;
use Swift\Events\EventDispatcher;
use Swift\Router\Event\OnBeforeRoutesCompileEvent;
use Swift\Router\Exceptions\NotFoundException;
use RuntimeException;

class Router implements RouterInterface {

	protected array $routeHarvest = array();

    /**
     * @var array Array of all routes (incl. named routes).
     */
    protected array $routes = [];

    /**
     * @var array Array of all named routes.
     */
    protected array $namedRoutes = [];

    /**
     * @var string Can be used to ignore leading part of the Request URL (if main file lives in subdirectory of host)
     */
    protected string $basePath = '';

    /**
     * @var array Array of default match types (regex helpers)
     */
    protected array $matchTypes = [
        'i'  => '[0-9]++',
        'a'  => '[0-9A-Za-z]++',
        'h'  => '[0-9A-Fa-f]++',
        '*'  => '.+?',
        '**' => '.++',
        ''   => '[^/\.]++'
    ];

    /**
     * Router constructor.
     *
     * @param Harvester $harvester
     * @param HTTPRequest $HTTPRequest
     * @param Configuration $configuration
     * @param EventDispatcher $dispatcher
     */
    public function __construct(
        private Harvester $harvester,
        private HTTPRequest $HTTPRequest,
        private Configuration $configuration,
        private EventDispatcher $dispatcher
    ) {
    }

    /**
	 * Bind harvested routes to object
	 *
	 * @throws Exception
	 */
	public function bindRoutes(): void {
		if (empty($this->routeHarvest)) {
			return;
		}

		foreach ($this->routeHarvest as $route) {
			$this->addRoute($route);
		}
	}

	/**
	 * Get route from current url
	 *
	 * @return Route
	 * @throws Exception
	 */
	public function getCurrentRoute(): Route {
		$this->routeHarvest = $this->harvester->harvestRoutes();

        /**
         * @var OnBeforeRoutesCompileEvent $onBeforeCompileRoutes
         */
		$onBeforeCompileRoutes = $this->dispatcher->dispatch( new OnBeforeRoutesCompileEvent($this->routeHarvest, $this->matchTypes) );

        /**
         * Reassign possibly changed routes and match types
         */
		$this->routeHarvest = $onBeforeCompileRoutes->getRoutes();
		$this->matchTypes   = $onBeforeCompileRoutes->getMatchTypes();

		$this->bindRoutes();
		$match = $this->match();

		if (is_null($match)) {
			throw new NotFoundException('Not found');
		}

		return $match;
	}

    /**
     * Match a given Request Url against stored routes
     *
     * @param string|null $requestUrl
     * @param string|null $requestMethod
     *
     * @return Route|null Matched Route object with information on success, false on failure (no match).
     */
	public function match(string $requestUrl = null, string $requestMethod = null): ?Route {
		$params = [];

		// set Request Url if it isn't passed as parameter
		$requestUrl = is_null($requestUrl) ? trim($this->HTTPRequest->request->getRequest()['REQUEST_URI'], '/') : $requestUrl;

		// strip base path from request url
		$requestUrl = substr($requestUrl, strlen($this->basePath));
        $requestUrl = str_replace( array( $this->configuration->get( 'routing.baseurl' ), '/api' ), '', $requestUrl );

		// Strip query string (?a=b) from Request Url
		if (($strpos = strpos($requestUrl, '?')) !== false) {
			$requestUrl = substr($requestUrl, 0, $strpos);
		}

		// Remove trailing slash if not root url
		if ($requestUrl !== '/' && substr($requestUrl, -1) === '/') {
			$requestUrl = substr($requestUrl, 0, -1);
		}
		$requestUrl = trim($requestUrl, '/');

		$lastRequestUrlChar = $requestUrl[strlen($requestUrl) - 1];

		// set Request Method if it isn't passed as a parameter
		$requestMethod = is_null($requestMethod) ? $this->HTTPRequest->request->getMethod() : $requestMethod;

		foreach ($this->routes as $handler) {
			$method_match = $handler->methodApplies($requestMethod);

			// Method did not match, continue to next route.
			if (!$method_match) {
				continue;
			}

			if ($handler->getFullRegex() === '*') {
				// * wildcard (matches all)
				$match = true;
			} elseif (isset($handler->getFullRegex()[0]) && $handler->getFullRegex()[0] === '@') {
				// @ regex delimiter
				$pattern = '`' . substr($handler->getFullRegex(), 1) . '`u';
				$match   = preg_match($pattern, $requestUrl, $params) === 1;
			} elseif (($position = strpos($handler->getFullRegex(), '[')) === false) {
				// No params in url, do string comparison
				$match = strcmp(trim($requestUrl, '/'), trim($handler->getFullRegex(), '/')) === 0;
			} else {
				// Compare longest non-param string with url before moving on to regex
				// Check if last character before param is a slash, because it could be optional if param is optional too (see https://github.com/dannyvankooten/AltoRouter/issues/241)
				if (strncmp($requestUrl, $handler->getFullRegex(), $position) !== 0 && ($lastRequestUrlChar === '/' || $handler->getFullRegex()[$position - 1] !== '/')) {
					continue;
				}

				$regex = $this->compileRoute($handler->getFullRegex());
				$match = preg_match($regex, $requestUrl, $params) === 1;
			}

			if ($match) {
				if ($params) {
					foreach ($params as $key => $value) {
						if (is_numeric($key)) {
							unset($params[$key]);
						}
					}
				}

				$handler->params = $params;

				return $handler;
			}
		}

		return null;
	}

    /**
     * Retrieve array of all available routes
     *
     * @return array
     */
    public function getRoutes(): array {
        return $this->routes;
    }

    /**
     * Add multiple routes at once
     *
     * @param array $routes
     */
    public function addRoutes( array $routes ): void {
        foreach($routes as $route) {
            $this->addRoute($route);
        }
    }

    /**
     * Add a route
     *
     * @param Route $route
     */
    public function addRoute( Route $route ): void {
        $this->routes[] = $route;

        if ($route->name) {
            if (isset($this->namedRoutes[$route->name])) {
                throw new RuntimeException("Can not redeclare route '{$route->name}'");
            }
            $this->namedRoutes[$route->name] = $route;
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
        $this->matchTypes = array_merge($this->matchTypes, $matchTypes);
    }

    /**
     * @param array $matchTypes
     */
    public function setMatchTypes( array $matchTypes ): void {
        $this->matchTypes = $matchTypes;
    }

    /**
     * @param string $routeName The name of the route.
     * @param array $params @params Associative array of parameters to replace placeholders with.
     *
     * @return Route The Route object. If params are provided it will include the route with named parameters in place.
     */
    public function generate( string $routeName, array $params = array() ): Route {
        // Check if named route exists
        if (!isset($this->namedRoutes[$routeName])) {
            throw new RuntimeException("Route '{$routeName}' does not exist.");
        }

        // Replace named parameters
        $route = $this->namedRoutes[$routeName];

        // prepend base path to route url again
        $url = $this->basePath . $route;

        if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $index => $match) {
                list($block, $pre, $type, $param, $optional) = $match;

                if ($pre) {
                    $block = substr($block, 1);
                }

                if (isset($params[$param])) {
                    // Part is found, replace for param value
                    $url = str_replace($block, $params[$param], $url);
                } elseif ($optional && $index !== 0) {
                    // Only strip preceding slash if it's not at the base
                    $url = str_replace($pre . $block, '', $url);
                } else {
                    // Strip match block
                    $url = str_replace($block, '', $url);
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
        if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER)) {
            $matchTypes = $this->matchTypes;
            foreach ($matches as $match) {
                list($block, $pre, $type, $param, $optional) = $match;

                if (isset($matchTypes[$type])) {
                    $type = $matchTypes[$type];
                }
                if ($pre === '.') {
                    $pre = '\.';
                }

                $optional = $optional !== '' ? '?' : null;

                //Older versions of PCRE require the 'P' in (?P<named>)
                $pattern = '(?:'
                           . ($pre !== '' ? $pre : null)
                           . '('
                           . ($param !== '' ? "?P<$param>" : null)
                           . $type
                           . ')'
                           . $optional
                           . ')'
                           . $optional;

                $route = str_replace($block, $pattern, $route);
            }
        }
        return "`^$route$`u";
    }
}