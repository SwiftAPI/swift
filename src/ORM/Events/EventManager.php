<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\ORM\Events;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Swift\Kernel\Attributes\Autowire;

/**
 * Class EventManager
 * @package Swift\ORM\Events
 */
#[Autowire]
class EventManager extends \Doctrine\Common\EventManager {

    /**
     * Map of registered listeners.
     * <event> => <listeners>
     *
     * @var object[][]
     */
    private array $_listeners = [];

    /**
     * Dispatches an event to all registered listeners.
     *
     * @param string $eventName The name of the event to dispatch. The name of the event is
     *                                  the name of the method that is invoked on listeners.
     * @param EventArgs|null $eventArgs The event arguments to pass to the event handlers/listeners.
     *                                  If not supplied, the single empty EventArgs instance is used.
     *
     * @return void
     */
    public function dispatchEvent( $eventName, ?EventArgs $eventArgs = null ): void {
        if ( ! isset( $this->_listeners[ $eventName ] ) ) {
            return;
        }

        $eventArgs = $eventArgs ?? EventArgs::getEmptyInstance();

        foreach ( $this->_listeners[ $eventName ] as [$methodName, $listener] ) {
            $listener->$methodName( $eventArgs );
        }
    }

    /**
     * Gets the listeners of a specific event or all listeners.
     *
     * @param string|null $event The name of the event.
     *
     * @return object[]|object[][] The event listeners for the specified event, or all event listeners.
     */
    public function getListeners( $event = null ): array {
        return $event ? $this->_listeners[ $event ] : $this->_listeners;
    }

    /**
     * Checks whether an event has any registered listeners.
     *
     * @param string $event
     *
     * @return bool TRUE if the specified event has any listeners, FALSE otherwise.
     */
    public function hasListeners( $event ): bool {
        return ! empty( $this->_listeners[ $event ] );
    }

    /**
     * Adds an EventSubscriber. The subscriber is asked for all the events it is
     * interested in and added as a listener for these events.
     *
     * @param EventSubscriber $subscriber The subscriber.
     *
     * @return void
     */
    public function addEventSubscriber( EventSubscriber $subscriber ): void {
        $this->addEventListener( $subscriber->getSubscribedEvents(), $subscriber );
    }

    /**
     * Adds an event listener that listens on the specified events.
     *
     * @param string|string[] $events The event(s) to listen on.
     * @param object $listener The listener object.
     *
     * @return void
     */
    public function addEventListener( $events, $listener, string $methodName = null ): void {
        // Picks the hash code related to that listener
        $hash = spl_object_hash( $listener );

        foreach ( (array) $events as $event ) {
            // Overrides listener if a previous one was associated already
            // Prevents duplicate listeners on same event (same instance only)
            $this->_listeners[ $event ][ $hash ] = array( $methodName ?? $event, $listener );
        }
    }

    /**
     * Removes an EventSubscriber. The subscriber is asked for all the events it is
     * interested in and removed as a listener for these events.
     *
     * @param EventSubscriber $subscriber The subscriber.
     *
     * @return void
     */
    public function removeEventSubscriber( EventSubscriber $subscriber ): void {
        $this->removeEventListener( $subscriber->getSubscribedEvents(), $subscriber );
    }

    /**
     * Removes an event listener from the specified events.
     *
     * @param string|string[] $events
     * @param object $listener
     *
     * @return void
     */
    public function removeEventListener( $events, $listener ): void {
        // Picks the hash code related to that listener
        $hash = spl_object_hash( $listener );

        foreach ( (array) $events as $event ) {
            unset( $this->_listeners[ $event ][ $hash ] );
        }
    }

}