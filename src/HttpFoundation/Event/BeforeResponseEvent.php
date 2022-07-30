<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Event;

use Psr\Http\Message\ResponseInterface;
use Swift\DependencyInjection\Attributes\DI;
use Swift\Events\AbstractEvent;

/**
 * Class BeforeResponseEvent
 * @package Swift\Http\Event
 */
#[DI(autowire: false)]
class BeforeResponseEvent extends AbstractEvent {

    protected static string $eventDescription = 'Before response is send';
    protected static string $eventLongDescription = '';

    private ResponseInterface $response;

    /**
     * BeforeResponseEvent constructor.
     *
     * @param ResponseInterface $response
     */
    public function __construct( ResponseInterface $response ) {
        $this->response = $response;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface {
        return $this->response;
    }

    /**
     * @param ResponseInterface $response
     */
    public function setResponse( ResponseInterface $response ): void {
        $this->response = $response;
    }

}