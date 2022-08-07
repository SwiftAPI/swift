<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\RateLimiter\Factory;

use Swift\DependencyInjection\Attributes\DI;
use Swift\Security\RateLimiter\DependencyInjection\RateLimiterDiTags;

#[DI( tags: [ RateLimiterDiTags::RATE_LIMITER_CONFIGURATION_FACTORY ] )]
interface RateLimiterConfigurationFactoryInterface {
    
    public function create( string $name, string $stateId ): ?RateLimiterConfigurationInterface;
    
}