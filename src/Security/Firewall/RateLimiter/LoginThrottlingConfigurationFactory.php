<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Firewall\RateLimiter;


use Swift\Configuration\ConfigurationInterface;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Security\RateLimiter\Factory\RateLimiterConfiguration;
use Swift\Security\RateLimiter\Factory\RateLimiterConfigurationInterface;

#[Autowire]
class LoginThrottlingConfigurationFactory implements \Swift\Security\RateLimiter\Factory\RateLimiterConfigurationFactoryInterface {
    
    public function __construct(
        protected ConfigurationInterface $configuration,
    ) {
    }
    
    public function create( string $name, string $stateId ): ?RateLimiterConfigurationInterface {
        if ($name !== 'login_throttling') {
            return null;
        }
    
        return new RateLimiterConfiguration(
            $name,
            'sliding_window',
            $stateId,
            $this->configuration->get( 'firewalls.main.login_throttling.max_attempts', 'security' ),
            // 15 minutes
            new \DateInterval( 'PT' . 60 * 15 . 'S' ),
        );
    }
    
}