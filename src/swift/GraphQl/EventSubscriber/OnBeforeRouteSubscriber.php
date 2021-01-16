<?php declare(strict_types=1);


namespace Swift\GraphQl\EventSubscriber;


use Swift\Authentication\Types\AuthenticationLevelsEnum;
use Swift\Events\EventDispatcher;
use Swift\GraphQl\Kernel;
use Swift\Router\Event\OnBeforeRoutesCompileEvent;
use Swift\Router\Route;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OnBeforeRouteSubscriber implements EventSubscriberInterface {

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
            OnBeforeRoutesCompileEvent::class => 'onBeforeRoutesCompile',
        );
    }

    /**
     * @param OnBeforeRoutesCompileEvent $event
     * @param string $eventClassName
     * @param EventDispatcher $eventDispatcher
     */
    public function onBeforeRoutesCompile( OnBeforeRoutesCompileEvent $event, string $eventClassName, EventDispatcher $eventDispatcher ): void {
        $event->addRoute(new Route(array(
            'name' => 'graphql',
            'regex' => 'graphql',
            'methods' => array('POST'),
            'controller' => Kernel::class,
            'action' => 'run',
            'authRequired' => false,
            'authLevel' => AuthenticationLevelsEnum::NONE,
        )));
    }


}