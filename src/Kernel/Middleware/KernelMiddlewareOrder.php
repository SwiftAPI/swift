<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Middleware;


class KernelMiddlewareOrder {
    
    public const REQUEST_PARSING = - 80;
    public const TIMEZONE        = - 50;
    public const DEPRECATION     = - 50;
    public const CORS            = - 45;
    public const AUTHENTICATION  = - 10;
    public const RATE_LIMIT      = - 5;
    public const REQUEST_LOGGING = 0;
    public const GRAPHQL         = 30;
    public const REST            = 40;
    
}