<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Middleware;


interface MiddlewareInterface extends \Psr\Http\Server\MiddlewareInterface {
    
    /**
     * Priority of the middleware. The middleware will be sorted by this value.
     *
     * @return int
     */
    public function getPriority(): int;
    
}