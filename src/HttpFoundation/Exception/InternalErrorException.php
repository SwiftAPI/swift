<?php declare(strict_types=1);
/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

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