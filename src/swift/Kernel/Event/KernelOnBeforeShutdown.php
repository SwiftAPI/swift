<?php declare(strict_types=1);


namespace Swift\Kernel\Event;

use Swift\Http\Response\Response;
use Swift\Router\HTTPRequest;
use Symfony\Contracts\EventDispatcher\Event;
use Swift\Kernel\Attributes\DI;

/**
 * Class KernelOnBeforeShutdown
 * @package Swift\Kernel\Event
 */
#[DI(exclude: true)]
class KernelOnBeforeShutdown extends Event {

    /**
     * KernelRequest constructor.
     *
     * @param $request
     */
    public function __construct(
        public HTTPRequest $request,
        public Response $response,
    ) {
    }

    /**
     * @return HTTPRequest
     */
    public function getRequest(): HTTPRequest {
        return $this->request;
    }

    public function getResponse( Response $response ): Response {
        return $this->response;
    }

}