<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use Swift\Configuration\Configuration;
use Swift\Controller\ControllerInterface;
use Swift\Events\EventDispatcher;
use Swift\HttpFoundation\Exception\BadRequestException;
use Swift\HttpFoundation\Response;
use Swift\HttpFoundation\ResponseInterface;
use Swift\HttpFoundation\ServerRequest;
use Swift\HttpFoundation\Event\BeforeResponseEvent;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Event\KernelOnBeforeShutdown;
use Swift\Kernel\Event\KernelRequestEvent;
use Swift\Router\Event\OnBeforeRouteEnterEvent;
use Swift\HttpFoundation\Exception\AccessDeniedException;
use Swift\HttpFoundation\Exception\InternalErrorException;
use Swift\HttpFoundation\Exception\NotAuthorizedException;
use Swift\HttpFoundation\Exception\NotFoundException;
use Swift\Router\Route;
use Swift\Router\Router;

/**
 * Class Application
 * @package Swift\Kernel
 */
#[Autowire]
class Kernel {

    /**
     * Application constructor.
     *
     * @param Router $router
     * @param Configuration $configuration
     * @param ServerRequest $request
     * @param EventDispatcher $dispatcher
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(
        private Router $router,
        private Configuration $configuration,
        private ServerRequest $request,
        private EventDispatcher $dispatcher,
        private ServiceLocatorInterface $serviceLocator,
    ) {
    }

    /**
     * Method to run application
     *
     * @throws Exception
     */
    public function run(): void {
        try {
            $route = ($this->dispatcher->dispatch( new KernelRequestEvent( $this->request, $this->router->getCurrentRoute() ) ))->getRoute();

            $response = $this->dispatch( $route );
        } catch ( NotFoundException ) {
            $response = new Response(status: Response::HTTP_NOT_FOUND);
        } catch( BadRequestException $exception) {
            $response = new Response($exception->getMessage(), status: Response::HTTP_BAD_REQUEST);
        } catch ( NotAuthorizedException ) {
            $response = new Response(status: Response::HTTP_UNAUTHORIZED);
        } catch ( InternalErrorException ) {
            $response = new Response(status: Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch ( AccessDeniedException $exception ) {
            $response = new Response($exception->getMessage(), Response::HTTP_FORBIDDEN);
        } catch ( Exception $exception ) {
            if ( $this->isDebug() ) {
                throw $exception;
            }

            $response = new Response(status: Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response->send();
        $this->shutdown($response);
    }

    /**
     * Method to dispatch requested route
     *
     * @param Route $route
     *
     * @return Response
     * @throws Exception
     */
    public function dispatch( Route $route ): Response {

        /** @var Route $route */
        $route = ( $this->dispatcher->dispatch( new OnBeforeRouteEnterEvent( $route ) ) )->getRoute();

        if ( ! $this->serviceLocator->has( $route->getController() ) ) {
            throw new NotFoundException( 'Not found' );
        }

        /** @var ControllerInterface $controller */
        $controller = $this->serviceLocator->get( $route->getController() );

        if ( $controller instanceof ControllerInterface ) {
            $controller->setRoute( $route );
        }

        if ( empty( $route->getAction() ) || ! method_exists( $controller, $route->getAction() ) ) {
            throw new NotFoundException(
                $this->isDebug() ? sprintf('Action %s not found on controller %s', $route->getAction(), $controller::class) : 'Action not found'
            );
        }

        /** @var ResponseInterface $response */
        $response = $controller->{$route->getAction()}( $route->getParams() );
        $response = ( $this->dispatcher->dispatch( new BeforeResponseEvent( $response ), BeforeResponseEvent::class ) )->getResponse();

        return $response;
    }

    /**
     * Shortcut method for gracefully quitting the application with the given response
     *
     * @param ResponseInterface $response
     */
    #[NoReturn]
    public function finalize( ResponseInterface $response ): void {
        $this->shutdown($response);
    }

    /**
     * Shut down application after outputting response
     *
     * @param ResponseInterface $response
     */
    #[NoReturn]
    private function shutdown( ResponseInterface $response ): void {
        $this->dispatcher->dispatch(event: new KernelOnBeforeShutdown(request: $this->request, response: $response));

        exit();
    }

    /**
     * @return bool
     */
    private function isDebug(): bool {
        return $this->configuration->get('app.debug', 'root');
    }
}