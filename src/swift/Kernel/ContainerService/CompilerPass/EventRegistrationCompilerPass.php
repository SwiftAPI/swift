<?php declare(strict_types=1);


namespace Swift\Kernel\ContainerService\CompilerPass;

use Swift\Events\EventDispatcher;
use Swift\Kernel\Attributes\DI;
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
        foreach ($container->getDefinitionsByTag('kernel.event_subscriber') as $eventSubscriber) {
            $container->get(EventDispatcher::class)->addSubscriber($container->get($eventSubscriber));
        }
    }
}

