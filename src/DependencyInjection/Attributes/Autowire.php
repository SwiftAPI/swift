<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\DependencyInjection\Attributes;


#[\Attribute( \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_PARAMETER | \Attribute::TARGET_PROPERTY )]
#[\AllowDynamicProperties]
class Autowire {
    
    public function __construct(
        public string|null $tag = null,
        public string|null $serviceId = null,
    ) {
    }
    
    /**
     * @return string|null
     */
    public function getTag(): string|null {
        return $this->tag;
    }
    
    /**
     * @return string|null
     */
    public function getServiceId(): string|null {
        return $this->serviceId;
    }
    
    
}