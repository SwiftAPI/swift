<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\ORM\DI;


use Swift\Events\Attribute\ListenTo;
use Swift\Kernel\Container\CompilerPass\PostCompilerPassInterface;
use Swift\ORM\Events\EventManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class DoctrineBridgeCompilerPass
 * @package Swift\ORM\DI
 */
class DoctrineBridgeCompilerPass implements PostCompilerPassInterface {

    /**
     * @inheritDoc
     */
    public function process( ContainerBuilder $container ): void {
        /** @var EventManager $doctrineEventManager */
        $doctrineEventManager = $container->get(EventManager::class);

        foreach ($container->getServiceInstancesByTag('doctrine.events.listener') as $item) {
            $reflection = $container->getReflectionClass($item::class);

            foreach ($reflection->getMethods() as $method) {
                if (!empty($method->getAttributes(ListenTo::class))) {
                    /** @var ListenTo $listenTo */
                    $listenTo = $method->getAttributes(ListenTo::class)[0]->newInstance();
                    $doctrineEventManager->addEventListener($listenTo->event, $item, $method->getName());
                }
            }
        }
    }
}