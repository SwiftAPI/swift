<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Event;

use Swift\HttpFoundation\ResponseInterface;
use Swift\Kernel\Attributes\DI;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class BeforeResponseEvent
 * @package Swift\Http\Event
 */
#[DI(autowire: false)]
class BeforeResponseEvent extends Event {

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