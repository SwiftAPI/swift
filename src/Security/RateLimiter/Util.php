<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\RateLimiter;


class Util {
    
    /**
     * Returns the current users UUID or the client IP if no user is logged in.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string
     */
    public static function getStateFromRequest( \Psr\Http\Message\ServerRequestInterface $request ): string {
        return $request->getAttribute( 'auth' )?->getUser()?->getUuid() ?? $request->getClientIp();
    }
    
}