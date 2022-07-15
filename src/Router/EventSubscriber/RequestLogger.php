<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router\EventSubscriber;

use Dibi\Exception;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Kernel\Event\KernelRequestEvent;
use Swift\Kernel\Utils\Environment;
use Swift\Orm\EntityManagerInterface;
use Swift\Router\Model\Entity\LogRequest;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class RequestLogger
 * @package Swift\Router\EventSubscriber
 */
#[Autowire]
class RequestLogger implements EventSubscriberInterface {
    
    /**
     * RequestSubscriber constructor.
     *
     * @param \Swift\Orm\EntityManagerInterface $entityManager
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }
    
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents(): array {
        return [
            KernelRequestEvent::class => 'onKernelRequest',
        ];
    }
    
    /**
     * @param KernelRequestEvent $event
     * @param string             $eventClassName
     * @param EventDispatcher    $eventDispatcher
     *
     * @return void
     * @throws Exception
     */
    public function onKernelRequest( KernelRequestEvent $event, string $eventClassName, EventDispatcher $eventDispatcher ): void {
        // There's no point in logging cli requests
        if ( Environment::isCli() && ! Environment::isRuntime() ) {
            return;
        }
        
        $request = $event->getRequest();
        
        $requestLog = new LogRequest();
        $requestLog->setIp( $request->getClientIp() );
        $requestLog->setOrigin( $request->getUri()->getPath() );
        $requestLog->setTime( new \DateTime() );
        $requestLog->setMethod( $request->getMethod() );
        $requestLog->setHeaders( (object) $request->getHeaders()->all() );
        $requestLog->setBody( (object) $request->getParsedBody() );
        $requestLog->setCode( 200 );
        
        $this->entityManager->persist( $requestLog );
        $this->entityManager->run();
    }
    
}