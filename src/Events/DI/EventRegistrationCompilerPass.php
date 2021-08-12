<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Events\DI;

use Swift\Events\Attribute\ListenTo;
use Swift\Events\EventDispatcher;
use Swift\Kernel\Container\CompilerPass\PostCompilerPassInterface;
use Swift\Kernel\KernelDiTags;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EventRegistrationCompilerPass
 * @package Swift\Events\DI
 */
class EventRegistrationCompilerPass implements PostCompilerPassInterface {

    /**
     * @inheritDoc
     */
    public function process( ContainerBuilder $container ) {
        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $container->get(EventDispatcher::class);

        // Get all event subscribers and register them by name
        foreach ($container->getDefinitions() as $definition) {
            if ($definition->hasTag(KernelDiTags::EVENT_SUBSCRIBER)) {
                $eventDispatcher->addSubscriber($container->get($definition->getClass()));
            }

            if ($definition->hasTag('events.listener')) {
                $reflection = $container->getReflectionClass($definition->getClass());
                foreach ($reflection->getMethods() as $reflectionMethod) {
                    if (!empty($reflectionMethod->getAttributes(ListenTo::class))) {
                        $attribute = $reflectionMethod->getAttributes(ListenTo::class)[0]->getArguments();
                        $eventDispatcher->addListener($attribute['event'], [$container->get($definition->getClass()), $reflectionMethod->getName()]);
                    }
                }
            }
        }
    }
}

