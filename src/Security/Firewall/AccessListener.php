<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Firewall;

use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Events\Attribute\ListenTo;
use Swift\Events\EventListenerInterface;
use Swift\HttpFoundation\Exception\NotAuthorizedException;
use Swift\HttpFoundation\Response;
use Swift\Kernel\Utils\Environment;
use Swift\Router\Router;
use Swift\Security\Authentication\Events\AuthenticationFinishedEvent;
use Swift\Security\Authorization\AccessDecisionManagerInterface;
use Swift\Security\Security;

/**
 * Class AccessListener
 * @package Swift\Security\Firewall
 */
#[Autowire]
class AccessListener implements EventListenerInterface {
    
    /**
     * AccessListener constructor.
     *
     * @param Security                       $security
     * @param AccessDecisionManagerInterface $accessDecisionManager
     * @param \Swift\Router\Router           $router
     */
    public function __construct(
        private readonly Security                       $security,
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
        private readonly Router                         $router,
    ) {
    }
    
    #[ListenTo( event: AuthenticationFinishedEvent::class )]
    public function onAuthenticationFinished( AuthenticationFinishedEvent $event ): void {
        if ( Environment::isRuntime() ) {
            return;
        }
        if ( ! empty( $this->router->getCurrentRoute()?->getIsGranted() ) &&
             ! $this->accessDecisionManager->decide( $this->security->getToken(), null, $this->router->getCurrentRoute()?->getIsGranted() )
        ) {
            throw new NotAuthorizedException( '', Response::HTTP_UNAUTHORIZED );
        }
        
        if ( ! empty( $this->router->getCurrentRoute()?->getControllerRoute()?->getIsGranted() ) &&
             ! $this->accessDecisionManager->decide( $this->security->getToken(), null, $this->router->getCurrentRoute()?->getControllerRoute()?->getIsGranted() )
        ) {
            throw new NotAuthorizedException( '', Response::HTTP_UNAUTHORIZED );
        }
    }
    
}