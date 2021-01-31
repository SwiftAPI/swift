<?php declare(strict_types=1);

namespace Swift\HttpFoundation\Exception;

use Psr\Http\Message\RequestInterface;
use RuntimeException;
use Swift\Kernel\Attributes\DI;

/**
 * Class InternalErrorException
 * @package Swift\Router\Exceptions
 */
#[DI(exclude: true)]
class InternalErrorException extends RuntimeException implements RequestExceptionInterface, \Psr\Http\Client\RequestExceptionInterface {

    protected $code = 500;

    /**
     * Returns the request.
     *
     * The request object MAY be a different object from the one passed to ClientInterface::sendRequest()
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface {
        // TODO: Implement getRequest() method.
    }
}