<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\RateLimiter\DependencyInjection;

class RateLimiterDiTags {
    
    public const RATE_LIMITER_CONFIGURATION_FACTORY = 'security.rate_limiter_configuration.factory';
    public const RATE_LIMITER_STRATEGY_FACTORY = 'security.rate_limiter_strategy.factory';
    
}
