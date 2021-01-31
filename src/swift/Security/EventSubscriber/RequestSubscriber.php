<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\EventSubscriber;

use Dibi\Exception;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Event\KernelRequestEvent;
use Swift\Security\Guard;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class RequestSubscriber
 * @package Swift\Security\EventSubscriber
 */
#[Autowire]
class RequestSubscriber implements EventSubscriberInterface {

    /**
     * RequestSubscriber constructor.
     *
     * @param Guard $guard
     */
    public function __construct(
        private Guard $guard,
    ) {
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents(): array {
        return array(
            KernelRequestEvent::class => 'onKernelRequest',
        );
    }

    /**
     * @param KernelRequestEvent $event
     * @param string $eventClassName
     * @param EventDispatcher $eventDispatcher
     *
     * @return void
     */
    public function onKernelRequest( KernelRequestEvent $event, string $eventClassName, EventDispatcher $eventDispatcher ): void {
        $this->guard->guard($event->getRequest(), $event->getRoute());
    }

}