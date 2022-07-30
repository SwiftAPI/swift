<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swift\Configuration\ConfigurationInterface;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Events\EventDispatcherInterface;
use Swift\HttpFoundation\Event\BeforeResponseEvent;
use Swift\HttpFoundation\Exception\AccessDeniedException;
use Swift\HttpFoundation\Exception\BadRequestException;
use Swift\HttpFoundation\Exception\InternalErrorException;
use Swift\HttpFoundation\Exception\NotAuthorizedException;
use Swift\HttpFoundation\Exception\NotFoundException;
use Swift\HttpFoundation\JsonResponse;
use Swift\HttpFoundation\Response;
use Swift\Kernel\Event\KernelOnBeforeShutdown;
use Swift\Kernel\Event\KernelRequestEvent;
use Swift\Kernel\Middleware\MiddlewareRunner;

#[Autowire]
final class Kernel implements KernelInterface {
    
    private bool $isRunning = false;
    
    /**
     * @param ConfigurationInterface                 $configuration
     * @param \Swift\Events\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        private readonly ConfigurationInterface   $configuration,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }
    
    /**
     * Method to run application
     *
     * @throws Exception
     */
    public function run( ServerRequestInterface $request, MiddlewareRunner $middlewareRunner ): void {
        if ( $this->isRunning ) {
            throw new \RuntimeException( 'Application is already running' );
        }
        
        if ( $this->isDebug() ) {
            $this->finalize( $request, $this->doRun( $request, $middlewareRunner ) );
        }
        
        try {
            $response = $this->doRun( $request, $middlewareRunner );
        } catch ( Exception $exception ) {
            $response = new JsonResponse( [ 'message' => $this->isDebug() ? $exception->getMessage() : Response::$reasonPhrases[ Response::HTTP_INTERNAL_SERVER_ERROR ], 'code' => $exception->getCode() ], status: Response::HTTP_INTERNAL_SERVER_ERROR );
        }
        
        $this->finalize( $request, $response );
    }
    
    protected function doRun( ServerRequestInterface $request, MiddlewareRunner $middlewareRunner ): \Psr\Http\Message\ResponseInterface {
        try {
            $this->eventDispatcher->dispatch( new KernelRequestEvent( $request ) );
            
            $response = $middlewareRunner->run( $request );
        } catch ( NotFoundException $exception ) {
            $response = new JsonResponse( [ 'message' => $exception->getMessage() ?: Response::$reasonPhrases[ Response::HTTP_NOT_FOUND ], 'code' => $exception->getCode() ], status: Response::HTTP_NOT_FOUND );
        } catch ( BadRequestException $exception ) {
            $response = new JsonResponse( [ 'message' => $exception->getMessage(), 'code' => $exception->getCode() ], status: Response::HTTP_BAD_REQUEST );
        } catch ( NotAuthorizedException $exception ) {
            $response = new JsonResponse( [ 'message' => $exception->getMessage() ?: Response::$reasonPhrases[ Response::HTTP_UNAUTHORIZED ], 'code' => $exception->getCode() ], status: Response::HTTP_UNAUTHORIZED );
        } catch ( InternalErrorException $exception ) {
            $response = new JsonResponse( [ 'message' => $this->isDebug() ? $exception->getMessage() : Response::$reasonPhrases[ Response::HTTP_INTERNAL_SERVER_ERROR ], 'code' => $exception->getCode() ], status: Response::HTTP_INTERNAL_SERVER_ERROR );
        } catch ( AccessDeniedException $exception ) {
            $response = new JsonResponse( [ 'message' => $exception->getMessage(), 'code' => $exception->getCode() ], Response::HTTP_FORBIDDEN );
        }
        
        return $response;
    }
    
    /**
     * @return bool
     */
    public function isDebug(): bool {
        return $this->configuration->get( 'app.debug', 'root' );
    }
    
    /**
     * Shortcut method for gracefully quitting the application with the given response
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param ResponseInterface                        $response
     *
     * @return never
     */
    #[NoReturn]
    public function finalize( ServerRequestInterface $request, ResponseInterface $response ): never {
        $response = ( $this->eventDispatcher->dispatch( new BeforeResponseEvent( $response ), BeforeResponseEvent::class ) )->getResponse();
        $response->send();
        $this->shutdown( $request, $response );
    }
    
    /**
     * Shut down application after outputting response
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param ResponseInterface                        $response
     */
    #[NoReturn]
    private function shutdown( ServerRequestInterface $request, ResponseInterface $response ): void {
        $this->eventDispatcher->dispatch( event: new KernelOnBeforeShutdown( request: $request, response: $response ) );
        
        exit();
    }
    
}