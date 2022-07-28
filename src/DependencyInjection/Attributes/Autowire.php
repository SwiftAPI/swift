<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\DependencyInjection\Attributes;


/**
 * Class Autowire
 * @package Swift\DependencyInjection\Attributes
 */
#[\Attribute( \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_PARAMETER | \Attribute::TARGET_PROPERTY )]
class Autowire {
    
    /**
     * Autowire constructor.
     *
     * @param string|null $tag
     * @param string|null $serviceId
     */
    public function __construct(
        public ?string $tag = null,
        public ?string $serviceId = null,
    ) {
    }
    
    /**
     * @return string|null
     */
    public function getTag(): ?string {
        return $this->tag;
    }
    
    /**
     * @return string|null
     */
    public function getServiceId(): ?string {
        return $this->serviceId;
    }
    
    
    
}