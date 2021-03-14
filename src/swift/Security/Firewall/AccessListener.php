<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Firewall;

use Swift\Events\Attribute\ListenTo;
use Swift\HttpFoundation\Exception\NotAuthorizedException;
use Swift\HttpFoundation\Response;
use Swift\Kernel\Attributes\Autowire;
use Swift\Router\Attributes\Route;
use Swift\Router\RouterInterface;
use Swift\Security\Authentication\Events\AuthenticationSuccessEvent;
use Swift\Security\Authorization\AccessDecisionManagerInterface;
use Swift\Security\Security;

/**
 * Class AccessListener
 * @package Swift\Security\Firewall
 */
#[Autowire]
class AccessListener {

    /**
     * AccessListener constructor.
     *
     * @param Firewall $firewall
     * @param Security $security
     * @param AccessDecisionManagerInterface $accessDecisionManager
     * @param RouterInterface $router
     */
    public function __construct(
        private Firewall $firewall,
        private Security $security,
        private AccessDecisionManagerInterface $accessDecisionManager,
        private RouterInterface $router,
    ) {
    }

    #[ListenTo(event: AuthenticationSuccessEvent::class)]
    public function onAuthenticationFinished( AuthenticationSuccessEvent $event ): void {
        if (!empty($this->router->getCurrentRoute()?->getIsGranted()) &&
            !$this->accessDecisionManager->decide($this->security->getToken(), null, $this->router->getCurrentRoute()?->getIsGranted())
        ) {
            throw new NotAuthorizedException('', Response::HTTP_UNAUTHORIZED);
        }

        if (!empty($this->router->getCurrentRoute()?->getControllerRoute()?->getIsGranted()) &&
            !$this->accessDecisionManager->decide($this->security->getToken(), null, $this->router->getCurrentRoute()?->getControllerRoute()?->getIsGranted())
        ) {
            throw new NotAuthorizedException('', Response::HTTP_UNAUTHORIZED);
        }
    }

}