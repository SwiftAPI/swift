<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\EventSubscriber;


use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Events\Attribute\ListenTo;

/**
 * Class OnKernelRequestSubscriber
 * @package Swift\Http\EventSubscriber
 */
#[Autowire]
final class OnKernelRequestSubscriber implements \Swift\Events\EventSubscriberInterface {
    
    /**
     * OnKernelRequestSubscriber constructor.
     *
     * @param \Swift\Configuration\ConfigurationInterface $configuration
     * @param \Swift\Kernel\Kernel                        $application
     */
    public function __construct(
        private readonly \Swift\Configuration\ConfigurationInterface $configuration,
        private readonly \Swift\Kernel\Kernel                        $application,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array {
        return [
            \Swift\Kernel\Event\KernelRequestEvent::class => 'onKernelRequest',
        ];
        
    }
    
    /**
     * Catch preflight requests and return appropriate CORS headers if enabled
     *
     * @param \Swift\Kernel\Event\KernelRequestEvent $event
     */
    #[ListenTo( event: \Swift\Kernel\Event\KernelRequestEvent::class )]
    public function onKernelRequest( \Swift\Kernel\Event\KernelRequestEvent $event ): void {
        if ( $this->configuration->get( 'app.allow_cors', 'root' ) && $event->getRequest()->isPreflight() ) {
            $response = new \Swift\HttpFoundation\CorsResponse();
            $response->sendOutput();
            $this->application->finalize( $response );
        }
    }
    
}