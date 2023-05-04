<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\RateLimiter\Attributes;

#[\Attribute( \Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD )]
#[\AllowDynamicProperties]
class RateLimit {
    
    public function __construct(
        protected readonly string $name,
    ) {
    }
    
    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }
    
    
    
}