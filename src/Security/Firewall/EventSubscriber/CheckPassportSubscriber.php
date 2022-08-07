<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Firewall\EventSubscriber;

use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Security\Firewall\Exception\LoginThrottlingTooManyAttempts;
use Swift\Security\User\Authentication\Passport\Stamp\LoginStamp;

#[Autowire]
class CheckPassportSubscriber implements \Swift\Events\EventSubscriberInterface {
    
    public function __construct(
        protected \Swift\Security\RateLimiter\RateLimiterFactory $rateLimiterFactory,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array {
        return [
            \Swift\Security\Authentication\Events\CheckPassportEvent::class => [
                'loginThrottling',
            ],
        ];
    }
    
    /**
     * Validate the login throttling.
     *
     * @param \Swift\Security\Authentication\Events\CheckPassportEvent $event
     *
     * @return void
     */
    public function loginThrottling( \Swift\Security\Authentication\Events\CheckPassportEvent $event ): void {
        if ( ! $event->getPassport()->hasStamp( LoginStamp::class ) ) {
            return;
        }
        
        $limiter = $this->rateLimiterFactory->create( 'login_throttling', $event->getPassport()->getUser()->getUuid() );
        $rate    = $limiter?->consume();
        
        if ( ! $rate->isAccepted() ) {
            throw new LoginThrottlingTooManyAttempts( $rate );
        }
        
    }
    
    
}
