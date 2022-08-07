<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\RateLimiter\Strategy;


use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Security\RateLimiter\Factory\RateLimiterConfigurationInterface;
use Swift\Security\RateLimiter\RateLimiterInterface;
use Swift\Security\RateLimiter\Storage\DatabaseTokenStorage;

#[Autowire]
class CoreStrategyFactory implements RateLimiterStrategyFactoryInterface {
    
    public function __construct(
        protected DatabaseTokenStorage $databaseTokenStorage,
    ) {
    }
    
    public function create( RateLimiterConfigurationInterface $configuration ): ?RateLimiterInterface {
        return match ( $configuration->getStrategy() ) {
            SlidingWindowStrategy::NAME, SlidingWindowStrategy::class => new SlidingWindowStrategy(
                $configuration->getName(),
                $configuration->getStateId(),
                $configuration->getLimit(),
                $configuration->getInterval(),
                $this->databaseTokenStorage,
            ),
            
            default => null,
        };
    }
    
}