<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\EventSubscriber;

use Swift\Configuration\Configuration;
use Swift\HttpFoundation\CorsResponse;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Kernel;
use Swift\Kernel\Event\KernelRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
    public function onKernelRequest( KernelRequestEvent $event ): void {
        $method = $event->getRequest()->getMethod();
        if ((($method === 'OPTIONS') || ($method === 'HEAD')) && $this->configuration->get(settingName: 'app.allow_cors', scope: 'root')) {
            $response = new CorsResponse();
            $response->sendOutput();
            $this->application->shutdown(response: $response);
        }

    }
}