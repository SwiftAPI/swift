<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Event;

use Psr\Http\Message\RequestInterface;
use Swift\DependencyInjection\Attributes\DI;
use Swift\Events\AbstractEvent;

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
        protected readonly RequestInterface $request,
    ) {
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface {
        return $this->request;
    }


}