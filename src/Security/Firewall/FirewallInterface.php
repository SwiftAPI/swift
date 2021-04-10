<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Firewall;

use Swift\Kernel\Event\KernelRequestEvent;

/**
 * Interface FirewallInterface
 * @package Swift\Security\Firewall
 */
interface FirewallInterface {

    /**
     * Startup the firewall
     *
     * @param KernelRequestEvent $kernelRequestEvent
     */
    public function start(KernelRequestEvent $kernelRequestEvent): void;

}