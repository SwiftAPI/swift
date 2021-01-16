<?php declare( strict_types=1 );

namespace Swift\Kernel;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use Swift\Configuration\Configuration;
use Swift\Controller\ControllerInterface;
use Swift\Events\EventDispatcher;
use Swift\Kernel\ContainerService\ContainerService;
use Swift\Http\Event\BeforeResponseEvent;
use Swift\Http\Response\JSONResponse;
use Swift\Http\Response\Response;
use Swift\Kernel\Event\KernelOnBeforeShutdown;
use Swift\Kernel\Event\KernelRequestEvent;
use Swift\Router\Event\OnBeforeRouteEnterEvent;
use Swift\Router\Exceptions\AccessDeniedException;
use Swift\Router\Exceptions\InternalErrorException;
use Swift\Router\Exceptions\NotAuthorizedException;
use Swift\Router\Exceptions\NotFoundException;
use Swift\Router\HTTPRequest;
use Swift\Router\Route;
use Swift\Router\Router;

class Application {

    /**
     * Application constructor.
     *
     * @param ContainerService|null $containerService
     * @param Router $router
     * @param Configuration $configuration
     * @param HTTPRequest $HTTPRequest
     * @param EventDispatcher $dispatcher
     */
    public function __construct(
        private Router $router,
        private Configuration $configuration,
        private HTTPRequest $HTTPRequest,
        private EventDispatcher $dispatcher,
        private ?ContainerService $containerService = null,
    ) {
        global $containerBuilder;
        $this->containerService = $containerBuilder;
    }

    /**
     * Method to run application
     *
     * @throws Exception
     */
    public function run(): void {
        try {
            $this->dispatcher->dispatch( new KernelRequestEvent( $this->HTTPRequest ) );

            $route    = $this->router->getCurrentRoute();
            $response = $this->dispatch( $route );
        } catch ( NotFoundException ) {
            $response = new JSONResponse();
            $response::notFound();
        } catch ( NotAuthorizedException ) {
            $response = new JSONResponse();
            $response::notAuthorized();
        } catch ( InternalErrorException ) {
            $response = new JSONResponse();
            $response::internalError();
        } catch ( AccessDeniedException $exception ) {
            $response = new JSONResponse($exception->getMessage());
        } catch ( Exception $exception ) {
            if ( $this->configuration->get( 'app.debug' ) ) {
                throw $exception;
            }

            $response = new JSONResponse();
            $response::internalError();
        }

        $response->sendOutput();
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
        if ( ! $this->containerService->has( $route->controller ) ) {
            throw new NotFoundException( 'Not found', 404 );
        }

        /** @var Route $route */
        $route = ( $this->dispatcher->dispatch( new OnBeforeRouteEnterEvent( $route ) ) )->getRoute();

        /** @var ControllerInterface $controller */
        $controller = $this->containerService->get( $route->controller );

        if ( $controller instanceof ControllerInterface ) {
            $controller->setRoute( $route );
        }

        if ( empty( $route->action ) || ! method_exists( $controller, $route->action ) ) {
            throw new NotFoundException( 'Action not found' );
        }

        /** @var Response $response */
        $response = $controller->{$route->action}( $route->params );
        $response = ( $this->dispatcher->dispatch( new BeforeResponseEvent( $response ), BeforeResponseEvent::class ) )->getResponse();

        return $response;
    }

    /**
     * Shut down application after outputting response
     *
     * @param Response $response
     */
    #[NoReturn]
    public function shutdown( Response $response ): void {
        $this->dispatcher->dispatch(event: new KernelOnBeforeShutdown(request: $this->HTTPRequest, response: $response));

        exit();
    }


}