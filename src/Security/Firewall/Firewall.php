<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Firewall;

use Swift\Configuration\ConfigurationInterface;
use Swift\HttpFoundation\RequestInterface;
use Swift\Events\Attribute\ListenTo;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Event\KernelRequestEvent;
use Swift\Router\RouterInterface;
use Swift\Security\Authentication\AuthenticationManager;
use Swift\Security\Authorization\AccessDecisionManagerInterface;
use Swift\Security\Security;

/**
 * Class Firewall
 * @package Swift\Security\Firewall
 */
#[Autowire]
class Firewall implements FirewallInterface {

    /**
     * Firewall constructor.
     *
     * @param AuthenticationManager $authenticationManager
     * @param AccessDecisionManagerInterface $accessDecisionManager
     * @param Security $security
     * @param RouterInterface $router
     * @param FirewallConfigInterface $firewallConfig
     * @param RequestInterface $request
     * @param ConfigurationInterface $configuration
     */
    public function __construct(
        private AuthenticationManager $authenticationManager,
        private AccessDecisionManagerInterface $accessDecisionManager,
        private Security $security,
        private RouterInterface $router,
        private FirewallConfigInterface $firewallConfig,
        private RequestInterface $request,
        private ConfigurationInterface $configuration,
    ) {
    }


    #[ListenTo(event: KernelRequestEvent::class, priority: -20)]
    public function start( KernelRequestEvent $kernelRequestEvent ): void {
        // Pre request execute
        // - Rate limiter
        // - Login throttling
        //

        if (!$this->configuration->get('app.allow_cors', 'root') ||
            ($this->configuration->get('app.allow_cors', 'root') && !$kernelRequestEvent->getRequest()->isPreflight())
        ) {
            $passport = $this->authenticationManager->authenticate($kernelRequestEvent->getRequest());
        }
    }

}