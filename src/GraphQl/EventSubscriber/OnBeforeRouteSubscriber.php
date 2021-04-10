<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\EventSubscriber;


use Swift\AuthenticationDeprecated\Types\AuthenticationLevelsEnum;
use Swift\Configuration\ConfigurationInterface;
use Swift\Events\EventDispatcher;
use Swift\GraphQl\Kernel;
use Swift\Kernel\Attributes\Autowire;
use Swift\Router\Event\OnBeforeRoutesCompileEvent;
use Swift\Router\Route;
use Swift\Security\Authorization\AuthorizationTypesEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OnBeforeRouteSubscriber
 * @package Swift\GraphQl\EventSubscriber
 */
#[Autowire]
class OnBeforeRouteSubscriber implements EventSubscriberInterface {

    /**
     * OnBeforeRouteSubscriber constructor.
     *
     * @param ConfigurationInterface $configuration
     */
    public function __construct(
        private ConfigurationInterface $configuration,
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
        if ($this->configuration->get('graphql.enabled', 'app')) {
            $event->addRoute(new Route(...array(
                'name' => 'graphql',
                'regex' => 'graphql',
                'methods' => array('POST'),
                'controller' => Kernel::class,
                'action' => 'run',
                'authType' => array(AuthorizationTypesEnum::PUBLIC_ACCESS),
                'isGranted' => array(),
            )));
        }
    }


}