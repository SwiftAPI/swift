<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\WebSocket\HttpFoundation;

/**
 * Interface MessageInterface
 * @package Swift\WebSocket\HttpFoundation
 */
interface MessageInterface {
    
    /**
     * Send output to socket
     *
     * @param \Swift\WebSocket\WsConnection $conn
     *
     * @return static
     */
    public function send( \Swift\WebSocket\WsConnection $conn ): static;
    
    public function __toString(): string;

}