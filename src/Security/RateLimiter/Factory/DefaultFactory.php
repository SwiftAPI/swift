<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\RateLimiter\Factory;


use Swift\DependencyInjection\Attributes\Autowire;

#[Autowire]
class DefaultFactory implements \Swift\Security\RateLimiter\Factory\RateLimiterConfigurationFactoryInterface {
    
    public function __construct(
        protected \Swift\Configuration\ConfigurationInterface $configuration,
    ) {
    }
    
    public function create( string $name, string $stateId ): ?RateLimiterConfigurationInterface {
        $config = $this->configuration->get( 'rate_limit', 'security' );
        
        if ( ! $config[ 'enable_default' ] || ( $name !== 'default' ) ) {
            return null;
        }
        
        return new RateLimiterConfiguration(
            'default',
            $config[ 'default_strategy' ],
            $stateId,
            $config[ 'default_limit' ],
            new \DateInterval( 'PT' . $config[ 'default_period' ] . 'S' ),
        );
        
    }
    
}