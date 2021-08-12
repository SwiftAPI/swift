<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use Swift\Configuration\Configuration;
use Swift\Controller\ControllerInterface;
use Swift\Events\EventDispatcher;
use Swift\HttpFoundation\Event\BeforeResponseEvent;
use Swift\HttpFoundation\Exception\AccessDeniedException;
use Swift\HttpFoundation\Exception\BadRequestException;
use Swift\HttpFoundation\Exception\InternalErrorException;
use Swift\HttpFoundation\Exception\NotAuthorizedException;
use Swift\HttpFoundation\Exception\NotFoundException;
use Swift\HttpFoundation\JsonResponse;
use Swift\HttpFoundation\Response;
use Swift\HttpFoundation\ResponseInterface;
use Swift\HttpFoundation\ServerRequest;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Container\Container;
use Swift\Kernel\Event\KernelOnBeforeShutdown;
use Swift\Kernel\Event\KernelRequestEvent;
use Swift\Kernel\Event\OnKernelRouteEvent;
use Swift\Router\Event\OnBeforeRouteEnterEvent;
use Swift\Router\Route;
use Swift\Router\RouteInterface;
use Swift\Router\Router;
use Swift\Security\Security;

/**
 * Class Application
 * @package Swift\Kernel
 */
#[Autowire]
final class Kernel {

    private Container $container;
    private bool $isRunning = false;

    /**
     * Application constructor.
     *
     * @param Router $router
     * @param Configuration $configuration
     * @param ServerRequest $request
     * @param EventDispatcher $dispatcher
     * @param Security $security
     */
    public function __construct(
        private Router $router,
        private Configuration $configuration,
        private ServerRequest $request,
        private EventDispatcher $dispatcher,
        private Security $security,
    ) {
    }

    /**
     * Method to run application
     *
     * @throws Exception
     */
    public function run(): void {
        if ( $this->isRunning ) {
            throw new \RuntimeException( 'Application is already running' );
        }

        try {
            $this->dispatcher->dispatch( new KernelRequestEvent( $this->request ) );
            $route = ( $this->dispatcher->dispatch( new OnKernelRouteEvent( $this->request, $this->router->getCurrentRoute() ) ) )->getRoute();

            $response = $this->dispatch( $route );
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
        } catch ( Exception $exception ) {
            if ( $this->isDebug() ) {
                throw $exception;
            }

            $response = new JsonResponse( [ 'message' => $this->isDebug() ? $exception->getMessage() : Response::$reasonPhrases[ Response::HTTP_INTERNAL_SERVER_ERROR ], 'code' => $exception->getCode() ], status: Response::HTTP_INTERNAL_SERVER_ERROR );
        }

        $this->finalize( $response );
    }

    /**
     * Method to dispatch requested route
     *
     * @param RouteInterface $route
     *
     * @return ResponseInterface
     * @throws Exception
     */
    private function dispatch( RouteInterface $route ): ResponseInterface {
        /** @var Route $route */
        $route = ( $this->dispatcher->dispatch( new OnBeforeRouteEnterEvent( $route ) ) )->getRoute();

        if ( ! $this->container->has( $route->getController() ) ) {
            throw new NotFoundException( 'Not found' );
        }

        /** @var ControllerInterface $controller */
        $controller = $this->container->get( $route->getController() );

        if ( $controller instanceof ControllerInterface ) {
            $controller->setRoute( $route );
        }

        if ( empty( $route->getAction() ) || ! method_exists( $controller, $route->getAction() ) ) {
            throw new NotFoundException(
                $this->isDebug() ? sprintf( 'Action %s not found on controller %s', $route->getAction(), $controller::class ) : 'Action not found'
            );
        }

        /** @var ResponseInterface $response */
        $response = $controller->{$route->getAction()}( $route->getParams() );

        return ( $this->dispatcher->dispatch( new BeforeResponseEvent( $response ), BeforeResponseEvent::class ) )->getResponse();
    }

    /**
     * @return bool
     */
    private function isDebug(): bool {
        return $this->configuration->get( 'app.debug', 'root' );
    }

    /**
     * Shortcut method for gracefully quitting the application with the given response
     *
     * @param ResponseInterface $response
     */
    #[NoReturn]
    public function finalize( ResponseInterface $response ): void {
        $response->send();
        $this->shutdown( $response );
    }

    /**
     * Shut down application after outputting response
     *
     * @param ResponseInterface $response
     */
    #[NoReturn]
    private function shutdown( ResponseInterface $response ): void {
        $this->dispatcher->dispatch( event: new KernelOnBeforeShutdown( request: $this->request, response: $response ) );

        exit();
    }

    #[Autowire]
    public function setContainer( #[Autowire( serviceId: 'service_container' )] Container $container ): void {
        $this->container = $container;
    }

}