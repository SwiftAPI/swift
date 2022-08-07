<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation;


class TooManyRequestsResponse extends JsonResponse {
    
    public function __construct( string $message = 'Too many requests' ) {
        parent::__construct( [ 'message' => $message ], Response::HTTP_TOO_MANY_REQUESTS );
    }
    
}