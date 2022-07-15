<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\WebSocket\HttpFoundation;


use function RingCentral\Psr7\parse_query;

class RequestParser {
    
    public function parseRequest( \Psr\Http\Message\RequestInterface $request ): \Swift\HttpFoundation\RequestInterface {
        $request->getBody()->rewind();
    
        $input = file_get_contents('php://input');
    
        if (is_string($input) && !empty($input)) {
            $input = json_decode( $input, true, 512, JSON_THROW_ON_ERROR );
        }
    
        $input = is_array($input) ? $input : [];
        return new \Swift\HttpFoundation\ServerRequest(
            parse_query($request->getUri()->getQuery()),
            $input,
            [],
            [],
            [],
            [],
        );
    }
    
}