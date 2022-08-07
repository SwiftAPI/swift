<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\RateLimiter\Strategy;

use Swift\DependencyInjection\Attributes\DI;
use Swift\Security\RateLimiter\DependencyInjection\RateLimiterDiTags;
use Swift\Security\RateLimiter\Factory\RateLimiterConfigurationInterface;
use Swift\Security\RateLimiter\RateLimiterInterface;

#[DI( tags: [ RateLimiterDiTags::RATE_LIMITER_STRATEGY_FACTORY ] )]
interface RateLimiterStrategyFactoryInterface {
    
    public function create( RateLimiterConfigurationInterface $configuration ): ?RateLimiterInterface;
    
}