<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\EventSubscriber;

use Swift\Configuration\Configuration;
use Swift\Events\Attribute\ListenTo;
use Swift\HttpFoundation\CorsResponse;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Kernel;
use Swift\Kernel\Event\KernelRequestEvent;
use Swift\Events\EventSubscriberInterface;

/**
 * Class OnKernelRequestSubscriber
 * @package Swift\Http\EventSubscriber
 */
#[Autowire]
final class OnKernelRequestSubscriber implements EventSubscriberInterface {

    /**
     * OnKernelRequestSubscriber constructor.
     *
     * @param Configuration $configuration
     * @param Kernel $application
     */
    public function __construct(
        private Configuration $configuration,
        private Kernel $application,
    ) {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array {
        return array(
            KernelRequestEvent::class => 'onKernelRequest'
        );

    }

    /**
     * Catch preflight requests and return appropriate CORS headers if enabled
     *
     * @param KernelRequestEvent $event
     */
    #[ListenTo(event: KernelRequestEvent::class)]
    public function onKernelRequest( KernelRequestEvent $event ): void {
        if ($this->configuration->get('app.allow_cors', 'root') && $event->getRequest()->isPreflight()) {
            $response = new CorsResponse();
            $response->sendOutput();
            $this->application->finalize($response);
        }
    }
}