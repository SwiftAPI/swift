<?php declare(strict_types=1);

namespace Swift\Router;

use ReflectionException;
use Swift\Authentication\Types\AuthenticationLevelsEnum;
use Swift\Router\Attributes\Route as RouteAttribute;
use Swift\Annotations\Annotations;
use Swift\Kernel\ContainerService\ContainerService;
use stdClass;


class Harvester
{

	/** @var ContainerService $containerService */
	private ContainerService $containerService;

    /**
     * Harvest constructor.
     *
     */
	public function __construct() {
	    global $containerBuilder;

		$this->containerService   = $containerBuilder;
	}

    /**
     * Method to harvest routes from annotations
     *
     * @return array
     * @throws ReflectionException
     */
	public function harvestRoutes(): array {
		$harvest = array();
		$controllers = $this->containerService->getDefinitionsByTag('kernel.controller');

		if (empty($controllers)) {
			return $harvest;
		}

		foreach ($controllers as $controller) {
		    $controller = $this->containerService->getReflectionClass($controller);

		    try {
                $constructAttr = $controller?->getMethod(name: '__construct')?->getAttributes(RouteAttribute::class);
            } catch ( ReflectionException) {
		        $constructAttr = null;
            }
		    $construct = !empty($constructAttr) ? $constructAttr[0]->getArguments() : null;

		    $controllerRoute = $construct ? $this->extractRoute($construct) : null;
			$baseRouteAuthRequired  = $construct ? (bool) $controllerRoute->authRequired : false;
			$baseRouteAuthLevel     = $construct ? $controllerRoute->authLevel : AuthenticationLevelsEnum::NONE;
			$baseRoute              = $construct ? $controllerRoute->regex : '';

			foreach ($controller?->getMethods() as $method) {
			    if ($method->getName() === '__construct') {
			        continue;
                }

                $methodAttr = $method?->getAttributes(RouteAttribute::class);
                $attribute = !empty($methodAttr) ? $methodAttr[0]->getArguments() : null;

                if (!$attribute) {
                    continue;
                }

				$route               = $this->extractRoute($attribute, $baseRoute);
				$route->authRequired = $baseRouteAuthRequired ? true : $route->authRequired;
				$route->authLevel    = $baseRouteAuthLevel === 'login' ? 'login' : $route->authLevel;
				$route->controller   = $controller?->getName();
				$route->action       = $method->name !== '__construct' ? $method->name : '';
				$harvest[]           = $route;
			}
		}

		return $harvest;
	}

    /**
     * Method to extract route from method annotation
     *
     * @param array $attributes
     * @param string $baseRoute
     *
     * @return Route
     */
	private function extractRoute( array $attributes, string $baseRoute = ''): Route {
		$baseRoute = trim($baseRoute, '/');
		$route = trim($attributes['route'], '/');

		$type = $attributes['type'];
		$authRequired   = array_key_exists(key: 'authRequired', array: $attributes) ? $attributes['authRequired'] : false;
		$authLevel      = array_key_exists(key: 'authLevel', array: $attributes) ? $attributes['authLevel'] : AuthenticationLevelsEnum::NONE;
		$name           = $attributes['name'] ?? null;

		return new Route(array(
            'regex' => $route,
            'controllerBase' => $baseRoute,
            'methods'  => explode('|', $type),
            'authRequired'  => $authRequired,
            'authLevel'     => $authLevel,
            'name'          => $name,
        ));
	}

}