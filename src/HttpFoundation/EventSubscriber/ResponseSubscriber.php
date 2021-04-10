<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\EventSubscriber;

use JetBrains\PhpStorm\ArrayShape;
use Swift\HttpFoundation\Event\BeforeResponseEvent;
use Swift\HttpFoundation\Response;
use Swift\Kernel\Attributes\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ResponseSubscriber
 * @package Swift\HttpFoundation\EventSubscriber
 */
#[Autowire]
final class ResponseSubscriber implements EventSubscriberInterface {

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
     * @return array The event names to listen to
     */
    #[ArrayShape( [ BeforeResponseEvent::class => "string" ] )]
    public static function getSubscribedEvents(): array {
        return array(
            BeforeResponseEvent::class => 'onBeforeResponseEvent',
        );
    }

    /**
     * @param BeforeResponseEvent $event
     * @param string $eventClassName
     * @param EventDispatcher $eventDispatcher
     *
     * @return Response
     */
    public function onBeforeResponseEvent( BeforeResponseEvent $event, string $eventClassName, EventDispatcher $eventDispatcher ) {

        return $event->getResponse();
    }

}