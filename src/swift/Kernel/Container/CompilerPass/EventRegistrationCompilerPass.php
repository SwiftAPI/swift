<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Container\CompilerPass;

use Swift\Events\Attribute\ListenTo;
use Swift\Events\EventDispatcher;
use Swift\Kernel\DiTags;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EventRegistrationCompilerPass
 * @package Swift\Kernel\ContainerService\CompilerPass
 */
class EventRegistrationCompilerPass implements CompilerPassInterface {

    /**
     * @inheritDoc
     */
    public function process( ContainerBuilder $container ) {
        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $container->get(EventDispatcher::class);

        // Get all event subscribers and register them by name
        foreach ($container->getDefinitions() as $definition) {
            if ($definition->hasTag(DiTags::EVENT_SUBSCRIBER)) {
                $eventDispatcher->addSubscriber($container->get($definition->getClass()));
            }

            $reflection = $container->getReflectionClass($definition->getClass());
            foreach ($reflection->getMethods() as $reflectionMethod) {
                if (!empty($reflectionMethod->getAttributes(ListenTo::class))) {
                    $attribute = $reflectionMethod->getAttributes(ListenTo::class)[0]->getArguments();
                    $eventDispatcher->addListener($attribute['event'], [$container->get($definition->getClass()), $reflectionMethod->getName()]);
                    //$eventDispatcher->addListener($attribute['event'], [$subscriber, $params])
                }
            }
        }
    }
}

