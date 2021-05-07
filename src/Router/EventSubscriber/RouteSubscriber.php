<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router\EventSubscriber;

use Swift\Kernel\Attributes\Autowire;
use Swift\Router\Event\OnBeforeRouteEnterEvent;
use Swift\Router\HTTPRequest;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class RouteSubscriber
 * @package Swift\Router\EventSubscriber
 */
#[Autowire]
class RouteSubscriber implements EventSubscriberInterface {

    /**
     * RequestSubscriber constructor.
     *
     * @param HTTPRequest $HTTPRequest
     */
    public function __construct(
        private HTTPRequest $HTTPRequest

    ) {
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
     *
     * @return void
     */
    public function onBeforeRouteEnter( OnBeforeRouteEnterEvent $event, string $eventClassName, EventDispatcher $eventDispatcher ): void {
    }

}