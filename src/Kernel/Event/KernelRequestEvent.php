<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Event;

use Swift\Events\AbstractEvent;
use Swift\HttpFoundation\RequestInterface;
use Swift\Kernel\Attributes\DI;
use Swift\Router\RouteInterface;

/**
 * Class KernelRequestEvent
 * @package Swift\Kernel\Event
 */
#[DI(autowire: false)]
class KernelRequestEvent extends AbstractEvent {

    protected static string $eventDescription = 'Entry into Kernel, before routing and authentication has started';
    protected static string $eventLongDescription = '';

    /**
     * KernelRequest constructor.
     *
     * @param RequestInterface $request
     */
    public function __construct(
        private RequestInterface $request,
    ) {
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface {
        return $this->request;
    }


}