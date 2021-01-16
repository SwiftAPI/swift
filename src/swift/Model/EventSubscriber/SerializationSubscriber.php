<?php declare(strict_types=1);


namespace Swift\Model\EventSubscriber;


use Swift\Events\EventDispatcher;
use Swift\Model\Events\EntityOnFieldSerializeEvent;
use Swift\Model\Events\EntityOnFieldUnSerializeEvent;
use Swift\Model\Utilities\Serialize;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SerializationSubscriber implements EventSubscriberInterface {

    private Serialize $serializer;

    /**
     * SerializationSubscriber constructor.
     *
     * @param Serialize $serializer
     */
    public function __construct( Serialize $serializer ) {
        $this->serializer = $serializer;
    }


    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * ['eventName' => 'methodName']
     *  * ['eventName' => ['methodName', $priority]]
     *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents() {
        return array(
            EntityOnFieldSerializeEvent::class => 'onSerializeField',
            EntityOnFieldUnSerializeEvent::class => 'onUnSerializeField',
        );
    }

    /**
     * @param EntityOnFieldSerializeEvent $event
     * @param string $eventClassName
     * @param EventDispatcher $eventDispatcher
     */
    public function onSerializeField( EntityOnFieldSerializeEvent $event, string $eventClassName, EventDispatcher $eventDispatcher ): void {
        if (method_exists($this->serializer, $event->action)) {
            $event->value = $this->serializer->{$event->action}($event->value, true);
        }
    }

    /**
     * @param EntityOnFieldUnserializeEvent $event
     * @param string $eventClassName
     * @param EventDispatcher $eventDispatcher
     */
    public function onUnSerializeField( EntityOnFieldUnserializeEvent $event, string $eventClassName, EventDispatcher $eventDispatcher ): void {
        if (method_exists($this->serializer, $event->action)) {
            $event->value = $this->serializer->{$event->action}($event->value, false);
        }
    }


}