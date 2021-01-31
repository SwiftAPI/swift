<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router\Attributes;

use Attribute;
use JetBrains\PhpStorm\Pure;
use Swift\AuthenticationDeprecated\Types\AuthenticationLevelsEnum;
use Swift\Router\Types\RouteTypesEnum;

/**
 * Class Route
 * @package Swift\Annotations\Annotation
 */
#[Attribute( Attribute::TARGET_CLASS|Attribute::TARGET_METHOD )]
class Route {

    /**
     * Route constructor.
     *
     * @param string|array $type
     * @param string|null $route
     * @param string|null $name
     * @param bool|null $authRequired
     * @param array|string|null $authLevel
     */
    #[Pure] public function __construct(
        public string|array $type = '',
        public string|null $route = null,
        public string|null $name = null,
        public bool|null $authRequired = false,
        public array|string|null $authLevel = null,
    ) {
        $this->type = is_array($this->type) ? $this->type : explode('|', $this->type);
        foreach ($this->type as $typeItem) {
            $routeType = new RouteTypesEnum($typeItem);
        }
        if (!is_null($this->authLevel)) {
            $this->authLevel = is_array($this->authLevel) ? $this->authLevel : explode('|', $this->authLevel);

            foreach($this->authLevel as $authLevelItem) {
                $routeAuthLevel = new AuthenticationLevelsEnum($authLevelItem);
            }
        }

        $this->authLevel ??= array(AuthenticationLevelsEnum::NONE);
    }
}