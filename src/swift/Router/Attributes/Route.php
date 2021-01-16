<?php declare( strict_types=1 );

namespace Swift\Router\Attributes;

use Attribute;
use Swift\Authentication\Types\AuthenticationLevelsEnum;
use Swift\Router\Types\RouteTypesEnum;

/**
 * Class Route
 * @package Swift\Annotations\Annotation
 */
#[Attribute( Attribute::TARGET_METHOD )]
class Route {


    public function __construct(
        /** @var string $type */
        public string $type = '',

        /** @var string|null $route */
        public string|null $route = null,

        /** @var string|null $name */
        public string|null $name = null,

        /** @var bool|null $authRequired */
        public bool|null $authRequired = false,

        /** @var string|null */
        public string|null $authLevel = null,
    ) {
        $routeType = new RouteTypesEnum($this->type);
        $routeAuthLevel = new AuthenticationLevelsEnum($this->authLevel);

        $this->authLevel ??= AuthenticationLevelsEnum::NONE;
    }
}