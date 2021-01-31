<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\AuthenticationDeprecated\EventSubscriber;

use Swift\AuthenticationDeprecated\Auth;
use Swift\Kernel\Attributes\Autowire;
use Swift\Router\Event\OnBeforeRouteEnterEvent;
use Swift\Router\Route;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class RouteSubscriber
 * @package Swift\AuthenticationDeprecated\EventSubscriber
 */
#[Autowire]
final class RouteSubscriber implements EventSubscriberInterface {

    /**
     * @var Auth $auth
     */
    private Auth $auth;

    /**
     * RouteSubscriber constructor.
     *
     * @param Auth $auth
     */
    public function __construct( Auth $auth ) {
        $this->auth = $auth;
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
     * The code must not depend on runtime state as it will only be called at compile time.
     * All logic depending on runtime state must be put into the individual methods handling the events.
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
     * @throws \Dibi\Exception
     */
    public function onBeforeRouteEnter( OnBeforeRouteEnterEvent $event, string $eventClassName, EventDispatcher $eventDispatcher ): void {

        /**
         * @var Route $route
         */
        $route = $event->getRoute();

        if ($route->isAuthRequired()) {
            $this->auth->validate('secret', $route->getAuthLevels());
        }
    }

}