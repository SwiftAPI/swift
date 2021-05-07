<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router\Attributes;

use Attribute;
use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Pure;
use Swift\Router\Types\RouteMethodEnum;
use Swift\Security\Authorization\AuthorizationTypesEnum;

/**
 * Class Route
 * @package Swift\Annotations\Annotation
 */
#[Attribute( Attribute::TARGET_CLASS|Attribute::TARGET_METHOD )]
class Route {

    public const TAG_ENTRYPOINT = 'ENTRYPOINT';

    /**
     * Route constructor.
     *
     * @param string|array $method
     * @param string $route The route for this method with a leading and closing slash
     * @param string $name Make the route easy to find back in the router and allow for reversed routing
     * @param string|array|null $type
     * @param array|string|null $authType
     * @param array|string|null $isGranted Validate user is granted certain rights or status. More on this in de Security documentation
     * @param array $tags Provide a route with certain tags. E.g. the Security component uses this to define a route as authentication endpoint
     */
    #[Pure] public function __construct(
        public string|array $method,
        public string $route,
        public string $name,
        #[Deprecated(replacement: 'Use "method" instead')] public string|array|null $type = null,
        public array|string|null $authType = null,
        public array|string|null $isGranted = null,
        public array $tags = array(),
    ) {
        $this->type = is_array($this->type) ? $this->type : explode('|', $this->type);
        $this->method = is_array($this->method) ? $this->method : explode('|', $this->method);
        if (!is_null($this->type)) {
            $this->method = array_unique(array_merge($this->method, $this->type));
        }
        foreach ($this->method as $method) {
            $routeType = new RouteMethodEnum($method);
        }
        if (!is_null($this->authType)) {
            $this->authType = is_array($this->authType) ? $this->authType : explode('|', $this->authType);

            foreach($this->authType as $authLevelItem) {
                $routeAuthLevel = new AuthorizationTypesEnum($authLevelItem);
            }
        }

        $this->authType ??= array(AuthorizationTypesEnum::PUBLIC_ACCESS);
    }
}