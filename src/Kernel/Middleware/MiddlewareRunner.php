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
     * @param \Psr\Http\Server\MiddlewareInterface[] $queue
     */
    public function __construct(
        protected MiddlewareQueue $queue,
    ) {
    }
    
    public function run(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $this->queue->compile();
        
        return $this->call( $request, null, 0 );
    }
    
    protected function call(
        ServerRequestInterface $request,
        ?ResponseInterface     $response,
        int                    $key,
    ): ResponseInterface {
        $middlewares = $this->queue;
        
        if ( ! $middlewares->has( $key ) ) {
            return $response;
        }
        
        return $middlewares->get( $key )->process(
            $request,
            new DecoratingRequestHandler( function ( ServerRequestInterface $request ) use ( $response, $key ) {
                $response = $this->call( $request, $response, $key + 1 );
                
                return $response;
            } ),
        );
    }
    
}