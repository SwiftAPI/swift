<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel;


use Psr\Http\Message\ServerRequestInterface;
use Swift\HttpFoundation\ResponseInterface;
use Swift\Kernel\Middleware\MiddlewareRunner;

interface KernelInterface {
    
    public function run( ServerRequestInterface $request, MiddlewareRunner $middlewareRunner ): void;
    
    public function isDebug(): bool;
    
    public function finalize( ServerRequestInterface $request, ResponseInterface $response ): void;
    
    
}