<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Events;

use Swift\Code\ReflectionFactory;
use Swift\Events\Attribute\ListenTo;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Kernel\KernelDiTags;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

#[Autowire]
class EventDispatcher extends SymfonyEventDispatcher implements EventDispatcherInterface {
    
    public function __construct(
        private ReflectionFactory $reflectionFactory,
    ) {
        parent::__construct();
    }
    
    #[Autowire]
    public function autowireEventSubscribers( #[Autowire( tag: KernelDiTags::EVENT_SUBSCRIBER )] ?iterable $eventSubscribers ): void {
        if (empty($eventSubscribers)) {
            return;
        }
    
        /** @var EventSubscriberInterface[] $eventSubscribers */
        $eventSubscribers = iterator_to_array($eventSubscribers);
        
        foreach ($eventSubscribers as $eventSubscriber) {
            $this->addSubscriber( $eventSubscriber );
        }
    }
    
    #[Autowire]
    public function autowireEventListeners( #[Autowire( tag: KernelDiTags::EVENT_LISTENER )] ?iterable $eventListeners ): void {
        if (empty($eventListeners)) {
            return;
        }
        
        /** @var \Swift\Events\EventListenerInterface[] $eventListeners */
        $eventListeners = iterator_to_array( $eventListeners );
        
        foreach ($eventListeners as $eventListener) {
            $reflection = $this->reflectionFactory->getReflectionClass( $eventListener::class );
            foreach ($reflection->getMethods() as $reflectionMethod) {
                if (!empty($reflectionMethod->getAttributes(ListenTo::class))) {
                    $attribute = $reflectionMethod->getAttributes(ListenTo::class)[0]->getArguments();
                    $this->addListener($attribute['event'], [$eventListener, $reflectionMethod->getName()]);
                }
            }
        }
    }

}