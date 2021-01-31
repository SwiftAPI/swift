<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation;

use DateTime;
use JetBrains\PhpStorm\Pure;
use Swift\Kernel\Attributes\DI;

/**
 * HeaderBag is a container for HTTP headers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
#[DI( exclude: true, autowire: false )]
class HeaderBag implements \IteratorAggregate, \Countable {

    protected const UPPER = '_ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected const LOWER = '-abcdefghijklmnopqrstuvwxyz';

    protected array $headers = [];
    protected array $cacheControl = [];

    public function __construct( array $headers = [] ) {
        foreach ( $headers as $key => $values ) {
            $this->set( $key, $values );
        }
    }

    /**
     * Sets a header by name.
     *
     * @param string|string[] $values The value or an array of values
     * @param bool $replace Whether to replace the actual value or not (true by default)
     */
    public function set( string $key, $values, bool $replace = true ): void {
        $key = strtr( $key, self::UPPER, self::LOWER );

        if ( \is_array( $values ) ) {
            $values = array_values( $values );

            if ( true === $replace || ! isset( $this->headers[ $key ] ) ) {
                $this->headers[ $key ] = $values;
            } else {
                $this->headers[ $key ] = array_merge( $this->headers[ $key ], $values );
            }
        } else {
            if ( true === $replace || ! isset( $this->headers[ $key ] ) ) {
                $this->headers[ $key ] = [ $values ];
            } else {
                $this->headers[ $key ][] = $values;
            }
        }

        if ( 'cache-control' === $key ) {
            $this->cacheControl = $this->parseCacheControl( implode( ', ', $this->headers[ $key ] ) );
        }
    }

    /**
     * Parses a Cache-Control HTTP header.
     *
     * @return array An array representing the attribute values
     */
    protected function parseCacheControl( string $header ): array {
        $parts = HeaderUtils::split( $header, ',=' );

        return HeaderUtils::combine( $parts );
    }

    /**
     * Returns the headers as a string.
     *
     * @return string The headers
     */
    public function __toString(): string {
        if ( ! $headers = $this->all() ) {
            return '';
        }

        ksort( $headers );
        $max     = max( array_map( 'strlen', array_keys( $headers ) ) ) + 1;
        $content = '';
        foreach ( $headers as $name => $values ) {
            $name = ucwords( $name, '-' );
            foreach ( $values as $value ) {
                $content .= sprintf( "%-{$max}s %s\r\n", $name . ':', $value );
            }
        }

        return $content;
    }

    /**
     * Returns the headers.
     *
     * @param string|null $key The name of the headers to return or null to get them all
     *
     * @return array An array of headers
     */
     #[Pure] public function all( string $key = null ): array {
        if ( null !== $key ) {
            return $this->headers[ strtr( $key, self::UPPER, self::LOWER ) ] ?? [];
        }

        return $this->headers;
    }

    /**
     * Returns the parameter keys.
     *
     * @return array An array of parameter keys
     */
    #[Pure] public function keys(): array {
        return array_keys( $this->all() );
    }

    /**
     * Replaces the current HTTP headers by a new set.
     *
     * @param array $headers
     */
    public function replace( array $headers = [] ): void {
        $this->headers = [];
        $this->add( $headers );
    }

    /**
     * Adds new headers the current HTTP headers set.
     *
     * @param array $headers
     */
    public function add( array $headers ): void {
        foreach ( $headers as $key => $values ) {
            $this->set( $key, $values );
        }
    }

    /**
     * Returns true if the HTTP header is defined.
     *
     * @param string $key
     *
     * @return bool true if the parameter exists, false otherwise
     */
    #[Pure] public function has( string $key ): bool {
        return \array_key_exists( strtr( $key, self::UPPER, self::LOWER ), $this->all() );
    }

    /**
     * Returns true if the given HTTP header contains the given value.
     *
     * @param string $key
     * @param string $value
     *
     * @return bool true if the value is contained in the header, false otherwise
     */
    #[Pure] public function contains( string $key, string $value ): bool {
        return \in_array( $value, $this->all( $key ), true );
    }

    /**
     * Removes a header.
     *
     * @param string $key
     */
    public function remove( string $key ): void {
        $key = strtr( $key, self::UPPER, self::LOWER );

        unset( $this->headers[ $key ] );

        if ( 'cache-control' === $key ) {
            $this->cacheControl = [];
        }
    }

    /**
     * Returns the HTTP header value converted to a date.
     *
     * @param string $key
     * @param DateTime|null $default
     *
     * @return DateTime|\DateTimeInterface|null The parsed DateTime or the default value if the header does not exist
     *
     */
    public function getDate( string $key, DateTime $default = null ): DateTime|\DateTimeInterface|null {
        if ( null === $value = $this->get( $key ) ) {
            return $default;
        }

        if ( false === $date = DateTime::createFromFormat( \DATE_RFC2822, $value ) ) {
            throw new \RuntimeException( sprintf( 'The "%s" HTTP header is not parseable (%s).', $key, $value ) );
        }

        return $date;
    }

    /**
     * Returns a header value by name.
     *
     * @param string $key
     * @param string|null $default
     *
     * @return string|null The first header value or default value
     */
    #[Pure] public function get( string $key, string $default = null ): ?string {
        $headers = $this->all( $key );

        if ( ! $headers ) {
            return $default;
        }

        if ( null === $headers[0] ) {
            return null;
        }

        return (string) $headers[0];
    }

    /**
     * Adds a custom Cache-Control directive.
     *
     * @param string $key
     * @param mixed $value The Cache-Control directive value
     */
    public function addCacheControlDirective( string $key, $value = true ): void {
        $this->cacheControl[ $key ] = $value;

        $this->set( 'Cache-Control', $this->getCacheControlHeader() );
    }

    protected function getCacheControlHeader(): string {
        ksort( $this->cacheControl );

        return HeaderUtils::toString( $this->cacheControl, ',' );
    }

    /**
     * Returns true if the Cache-Control directive is defined.
     *
     * @param string $key
     *
     * @return bool true if the directive exists, false otherwise
     */
    #[Pure] public function hasCacheControlDirective( string $key ): bool {
        return \array_key_exists( $key, $this->cacheControl );
    }

    /**
     * Returns a Cache-Control directive value by name.
     *
     * @param string $key
     *
     * @return mixed The directive value if defined, null otherwise
     */
    #[Pure] public function getCacheControlDirective( string $key ): mixed {
        return $this->cacheControl[ $key ] ?? null;
    }

    /**
     * Removes a Cache-Control directive.
     *
     * @param string $key
     */
    public function removeCacheControlDirective( string $key ): void {
        unset( $this->cacheControl[ $key ] );

        $this->set( 'Cache-Control', $this->getCacheControlHeader() );
    }

    /**
     * Returns an iterator for headers.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator(): \ArrayIterator {
        return new \ArrayIterator( $this->headers );
    }

    /**
     * Returns the number of headers.
     *
     * @return int The number of headers
     */
    #[Pure] public function count(): int {
        return \count( $this->headers );
    }
}
