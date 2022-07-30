<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Firewall;

use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Events\EventListenerInterface;
use Swift\Events\Attribute\ListenTo;
use Swift\Kernel\Event\KernelRequestEvent;

/**
 * Class Firewall
 * @package Swift\Security\Firewall
 */
#[Autowire]
class Firewall implements FirewallInterface, EventListenerInterface {
    



    #[ListenTo(event: KernelRequestEvent::class, priority: -20)]
    public function start( KernelRequestEvent $kernelRequestEvent ): void {
        // Pre request execute
        // - Rate limiter
        // - Login throttling
        //

        
    }

}