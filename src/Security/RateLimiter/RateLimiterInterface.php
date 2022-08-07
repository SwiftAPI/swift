<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\RateLimiter;

use Swift\DependencyInjection\Attributes\DI;

#[DI( tags: [ 'security.rate_limiter' ] )]
interface RateLimiterInterface {
    
    /**
     * @param int $tokens
     *
     * @return \Swift\Security\RateLimiter\RateLimitInterface
     */
    public function consume( int $tokens = 1 ): RateLimitInterface;
    
    /**
     * Resets the limit
     *
     * @return void
     */
    public function reset(): void;
    
}