<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\WebSocket\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class SocketRoute {
    
    /**
     * @param string $route
     * @param string $name
     * @param string[] $authTypes
     * @param string[] $isGranted
     */
    public function __construct(
        private readonly string $route,
        private readonly string $name,
        private array           $authTypes = [],
        private readonly array  $isGranted = [],
    ) {
        foreach ($this->authTypes as $key => $authType) {
            if ( is_object( $authType ) && enum_exists( $authType::class ) ) {
                $this->authTypes[ $key ] = $authType->value;
            }
        }
    }
    
    /**
     * @return string
     */
    public function getRoute(): string {
        return $this->route;
    }
    
    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }
    
    /**
     * @return string[]
     */
    public function getAuthTypes(): array {
        return $this->authTypes;
    }
    
    /**
     * @return string[]
     */
    public function getIsGranted(): array {
        return $this->isGranted;
    }
    
    
    
}