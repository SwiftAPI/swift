<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\EventListeners;


use Swift\Configuration\ConfigurationInterface;
use Swift\Events\Attribute\ListenTo;
use Swift\Events\EventListenerInterface;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Kernel\Event\KernelRequestEvent;
use Swift\Security\Authentication\Utils;

/**
 * Class KernelRequestListener
 * @package Swift\Security\Authentication\EventListeners
 */
#[Autowire]
class KernelRequestListener implements EventListenerInterface {

    /**
     * KernelRequestListener constructor.
     */
    public function __construct(
        private readonly ConfigurationInterface $configuration,
    ) {
    }

    /**
     * Populate static configuration to be freely used in non-autowired class constructors
     *
     * @param KernelRequestEvent $event
     */
    #[ListenTo( event: KernelRequestEvent::class )]
    public function populateStaticConfiguration( KernelRequestEvent $event ): void {
        Utils::$TOKEN_VALIDITY = $this->configuration->get('firewalls.main.token.validity', 'security');
    }

}