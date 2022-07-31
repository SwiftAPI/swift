<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Middleware;

use Dibi\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swift\Configuration\ConfigurationInterface;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Kernel\Event\KernelRequestEvent;
use Swift\Kernel\Utils\Environment;
use Swift\Orm\EntityManagerInterface;
use Swift\Router\Model\Entity\LogRequest;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use function React\Async\async;


#[Autowire]
final class RequestLoggingMiddleware implements MiddlewareInterface {
    
    /**
     * @param \Swift\Orm\EntityManagerInterface           $entityManager
     * @param \Swift\Configuration\ConfigurationInterface $configuration
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ConfigurationInterface $configuration,
    ) {
    }
    
    public function getOrder(): int {
        return KernelMiddlewareOrder::REQUEST_LOGGING;
    }
    
    public function process( ServerRequestInterface $request, RequestHandlerInterface $handler ): ResponseInterface {
        // There's no point in logging cli requests
        if ( ! $this->configuration->get( 'app.log_requests', 'app' ) || ( Environment::isCli() && ! Environment::isRuntime() ) ) {
            return $handler->handle( $request );
        }
        
        async( function () use ( $request ) {
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
        } )();
        
        return $handler->handle( $request );
    }
    

    
}