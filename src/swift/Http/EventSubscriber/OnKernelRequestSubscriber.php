<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Http\EventSubscriber;

use Swift\Configuration\Configuration;
use Swift\Http\Response\CorsResponse;
use Swift\Kernel\Application;
use Swift\Kernel\Event\KernelRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OnKernelRequestSubscriber
 * @package Swift\Http\EventSubscriber
 */
class OnKernelRequestSubscriber implements EventSubscriberInterface {

    /**
     * OnKernelRequestSubscriber constructor.
     *
     * @param Configuration $configuration
     * @param Application $application
     */
    public function __construct(
        private Configuration $configuration,
        private Application $application,
    ) {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents() {
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
        $method = $event->getRequest()->request->getMethod();
        if ((($method === 'OPTIONS') || ($method === 'HEAD')) && $this->configuration->get(settingName: 'app.allow_cors', scope: 'root')) {
            $response = new CorsResponse();
            $response->sendOutput();
            $this->application->shutdown(response: $response);
        }

    }
}