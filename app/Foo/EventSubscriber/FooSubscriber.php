<?php declare(strict_types=1);


namespace Foo\EventSubscriber;

use Henri\Framework\Configuration\Configuration;
use Henri\Framework\Events\EventDispatcher;
use Henri\Framework\Router\Event\OnBeforeRouteEnterEvent;
use Swift\Kernel\Attributes\DI;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

#[DI(exclude: true)]
final class FooSubscriber implements EventSubscriberInterface {

    /**
     * @var Configuration $configuration
     */
    private $configuration;

    /**
     * FooSubscriber constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct( Configuration $configuration ) {
        $this->configuration = $configuration;
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
    public static function getSubscribedEvents(): array {
        return array(
            OnBeforeRouteEnterEvent::class => 'onBeforeRouteEnter',
        );
    }

    /**
     * @param OnBeforeRouteEnterEvent $event
     * @param string $eventClassName
     * @param EventDispatcher $eventDispatcher
     */
    public function onBeforeRouteEnter( OnBeforeRouteEnterEvent $event, string $eventClassName, EventDispatcher $eventDispatcher ) {
        // Read/modify event data or do some logging
    }

}