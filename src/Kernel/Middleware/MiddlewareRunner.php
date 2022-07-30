<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swift\Kernel\RequestHandler\DecoratingRequestHandler;

class MiddlewareRunner {
    
    /**
     * @param \Psr\Http\Server\MiddlewareInterface[] $middlewares
     */
    public function __construct(
        protected array $middlewares,
    ) {
    }
    
    public function run(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $this->prepare();
        
        return $this->call( $request, null, 0 );
    }
    
    protected function call(
        ServerRequestInterface $request,
        ?ResponseInterface     $response,
        int                    $key,
    ): ResponseInterface {
        $middlewares = $this->middlewares;
        
        if ( ! array_key_exists( $key, $middlewares ) ) {
            return $response;
        }
        
        return $middlewares[ $key ]->process(
            $request,
            new DecoratingRequestHandler( function ( ServerRequestInterface $request ) use ( $response, $key ) {
                $response = $this->call( $request, $response, $key + 1 );
                
                return $response;
            } ),
        );
    }
    
    protected function prepare(): void {
        $indexed    = [];
        $nonIndexed = [];
        foreach ( $this->middlewares as $middleware ) {
            if ( $middleware instanceof MiddlewareInterface ) {
                if ( ! isset( $indexed[ $middleware->getPriority() ] ) ) {
                    $indexed[ $middleware->getPriority() ] = [];
                }
                $indexed[ $middleware->getPriority() ][] = $middleware;
                continue;
            }
            if ( $middleware instanceof \Psr\Http\Server\MiddlewareInterface ) {
                $nonIndexed[] = $middleware;
            }
        }
        ksort( $indexed );
        $flatIndexed = [];
        foreach ( $indexed as $middlewares ) {
            foreach ( $middlewares as $middleware ) {
                $flatIndexed[] = $middleware;
            }
        }
        $middlewares = [ ...$flatIndexed, ...$nonIndexed ];
        
        $this->middlewares = $middlewares;
    }
    
}