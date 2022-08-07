<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Validator\Rules;

use Swift\Configuration\ConfigurationInterface;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Security\RateLimiter\RateLimiterFactory;
use Swift\Security\RateLimiter\Util;

#[Autowire]
class RateLimiterValidationRuleFactory implements ValidationRulesFactoryInterface {
    
    public function __construct(
        protected ConfigurationInterface $configuration,
        protected RateLimiterFactory     $rateLimiterFactory,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public function create( \Psr\Http\Message\ServerRequestInterface $request ): array {
        if ( $this->configuration->get( 'rate_limit.enabled', 'security' ) && $this->configuration->get('rate_limit.enable_default', 'security') ) {
            $limiter = $this->rateLimiterFactory->create( 'default', Util::getStateFromRequest( $request ) );
            
            if ( ! $limiter ) {
                return [];
            }
            
            return [
                new QueryComplexityRateLimiter( $request, $limiter ),
            ];
        }
        
        return [];
    }
}