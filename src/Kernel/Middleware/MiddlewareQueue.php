<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Middleware;


use JetBrains\PhpStorm\Pure;

class MiddlewareQueue implements \IteratorAggregate, \Countable {
    
    /**
     * middleware storage.
     */
    protected array $middlewares;
    
    public function __construct( array $middlewares = [] ) {
        $this->middlewares = $middlewares;
    }
    
    /**
     * Returns the middleware keys.
     *
     * @return array An array of middleware keys
     */
    #[Pure]
    public function keys(): array {
        return array_keys( $this->middlewares );
    }
    
    /**
     * Replaces the current middlewares by a new set.
     *
     * @param \Psr\Http\Server\MiddlewareInterface[] $middlewares
     */
    public function replace( array $middlewares = [] ): void {
        $this->middlewares = $middlewares;
    }
    
    /**
     * Adds middlewares.
     *
     * @param \Psr\Http\Server\MiddlewareInterface[] $middlewares
     */
    public function add( array $middlewares = [] ): void {
        $this->middlewares = array_replace( $this->middlewares, $middlewares );
    }
    
    /**
     * Sets a middleware by name.
     *
     * @param int                                  $key
     * @param \Psr\Http\Server\MiddlewareInterface $middleware
     */
    public function set( int $key, \Psr\Http\Server\MiddlewareInterface $middleware ): void {
        $this->middlewares[ $key ] = $middleware;
    }
    
    public function append( \Psr\Http\Server\MiddlewareInterface $middleware ): void {
        $this->middlewares[] = $middleware;
    }
    
    /**
     * Returns true if the middleware is defined.
     *
     * @param int $key
     *
     * @return bool true if the middleware exists, false otherwise
     */
    #[Pure]
    public function has( int $key ): bool {
        return \array_key_exists( $key, $this->middlewares );
    }
    
    /**
     * Removes a middleware.
     *
     * @param int $key
     */
    public function remove( int $key ): void {
        unset( $this->middlewares[ $key ] );
    }
    
    /**
     * Returns a middleware by name.
     *
     * @param int        $key
     * @param mixed|null $default The default value if the middleware key does not exist
     *
     * @return \Psr\Http\Server\MiddlewareInterface|null
     */
    #[Pure]
    public function get( int $key, ?\Psr\Http\Server\MiddlewareInterface $default = null ): ?\Psr\Http\Server\MiddlewareInterface {
        return \array_key_exists( $key, $this->middlewares ) ? $this->middlewares[ $key ] : $default;
    }
    
    
    /**
     * Returns an iterator for middlewares.
     *
     * @return \ArrayIterator<\Psr\Http\Server\MiddlewareInterface> An \ArrayIterator instance
     */
    public function getIterator(): \ArrayIterator {
        return new \ArrayIterator( $this->middlewares );
    }
    
    /**
     * Returns the number of middlewares.
     *
     * @return int The number of middlewares
     */
    #[Pure]
    public function count(): int {
        return \count( $this->middlewares );
    }
    
    
    public function compile(): void {
        $indexed           = [];
        $middlewares       = $this->middlewares;
        $this->middlewares = [];
        foreach ( $middlewares as $middleware ) {
            if ( $middleware instanceof MiddlewareInterface ) {
                if ( ! isset( $indexed[ $middleware->getOrder() ] ) ) {
                    $indexed[ $middleware->getOrder() ] = [];
                }
                $indexed[ $middleware->getOrder() ][] = $middleware;
                continue;
            }
            if ( $middleware instanceof \Psr\Http\Server\MiddlewareInterface ) {
                if ( ! isset( $indexed[ 0 ] ) ) {
                    $indexed[ 0 ] = [];
                }
                $indexed[ 0 ][] = $middleware;
            }
            
        }
        ksort( $indexed );
        
        foreach ( $indexed as $middlewares ) {
            foreach ( $middlewares as $middleware ) {
                $this->append( $middleware );
            }
        }
    }
    
    public function getMiddlewareNames(): array {
        return array_map( static fn( \Psr\Http\Server\MiddlewareInterface $middleware ) => $middleware::class, $this->middlewares );
    }
    
}