<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\RateLimiter;

use Swift\Configuration\ConfigurationInterface;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Security\RateLimiter\DependencyInjection\RateLimiterDiTags;
use Swift\Security\RateLimiter\Factory\RateLimiterConfigurationInterface;
use Swift\Security\RateLimiter\Storage\DatabaseTokenStorage;

#[Autowire]
class RateLimiterFactory implements RateLimiterFactoryInterface {
    
    /**
     * @param \Swift\Security\RateLimiter\Storage\DatabaseTokenStorage                       $databaseTokenStorage
     * @param \Swift\Configuration\ConfigurationInterface                                    $configuration
     * @param \Swift\Security\RateLimiter\Factory\RateLimiterConfigurationFactoryInterface[] $configurationFactories
     * @param \Swift\Security\RateLimiter\Strategy\RateLimiterStrategyFactoryInterface[]     $strategyFactories
     */
    public function __construct(
        protected DatabaseTokenStorage                                                               $databaseTokenStorage,
        protected ConfigurationInterface                                                             $configuration,
        #[Autowire( tag: RateLimiterDiTags::RATE_LIMITER_CONFIGURATION_FACTORY )] protected iterable $configurationFactories,
        #[Autowire( tag: RateLimiterDiTags::RATE_LIMITER_STRATEGY_FACTORY )] protected iterable      $strategyFactories,
    ) {
    }
    
    public function create( string $name, string $stateId ): ?RateLimiterInterface {
        $config = $this->getConfig( $name, $stateId ) ?? throw new \InvalidArgumentException( sprintf( 'Rate limit "%s" is not configured', $name ) );
        
        return $this->getStrategy( $config ) ?? throw new \InvalidArgumentException( sprintf( 'Rate limit "%s" has no strategy configured', $name ) );
    }
    
    protected function getConfig( string $name, string $stateId ): ?RateLimiterConfigurationInterface {
        foreach ( $this->configurationFactories as $factory ) {
            $result = $factory->create( $name, $stateId );
            if ( $result ) {
                return $result;
            }
        }
        
        return null;
    }
    
    public function getStrategy( RateLimiterConfigurationInterface $configuration ): ?RateLimiterInterface {
        foreach ( $this->strategyFactories as $factory ) {
            $result = $factory->create( $configuration );
            if ( $result ) {
                return $result;
            }
        }
        
        return null;
    }
    
}