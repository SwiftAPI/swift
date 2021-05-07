<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Event;

use Psr\Http\Message\ServerRequestInterface;
use Swift\Events\AbstractEvent;
use Swift\HttpFoundation\ResponseInterface;
use Swift\Kernel\Attributes\DI;

/**
 * Class KernelOnBeforeShutdown
 * @package Swift\Kernel\Event
 */
#[DI(exclude: true)]
class KernelOnBeforeShutdown extends AbstractEvent {

    protected static string $eventDescription = 'Before Kernel is terminated after sending response';
    protected static string $eventLongDescription = 'Last event before Kernel terminates after response has been sent';

    /**
     * KernelRequest constructor.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(
        public ServerRequestInterface $request,
        public ResponseInterface $response,
    ) {
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface {
        return $this->request;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function getResponse( ResponseInterface $response ): ResponseInterface {
        return $this->response;
    }


}