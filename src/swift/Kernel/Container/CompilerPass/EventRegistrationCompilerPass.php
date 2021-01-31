<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Container\CompilerPass;

use Swift\Events\EventDispatcher;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class EventRegistrationCompilerPass
 * @package Swift\Kernel\ContainerService\CompilerPass
 */
class EventRegistrationCompilerPass implements CompilerPassInterface {

    /**
     * @inheritDoc
     */
    public function process( ContainerBuilder $container ) {
        // Get all event subscribers and register them by name
        foreach ($container->getServicesByTag('kernel.event_subscriber') as $eventSubscriber) {
            $container->get(EventDispatcher::class)->addSubscriber($container->get($eventSubscriber));
        }
    }
}

