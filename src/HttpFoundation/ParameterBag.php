<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation;

use JetBrains\PhpStorm\Pure;
use Swift\HttpFoundation\Exception\BadRequestException;
use Swift\Kernel\Attributes\DI;

/**
 * ParameterBag is a container for key/value pairs.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
#[DI( exclude: true, autowire: false )]
class ParameterBag implements \IteratorAggregate, \Countable {

    /**
     * Parameter storage.
     */
    protected array $parameters;

    public function __construct( array $parameters = [] ) {
        $this->parameters = $parameters;
    }

    /**
     * Returns the parameters.
     *
     * @return array An array of parameters
     */
    public function all(/*string $key = null*/ ): array {
        $key = \func_num_args() > 0 ? func_get_arg( 0 ) : null;

        if ( null === $key ) {
            return $this->parameters;
        }

        if ( ! \is_array( $value = $this->parameters[ $key ] ?? [] ) ) {
            throw new BadRequestException( sprintf( 'Unexpected value for parameter "%s": expecting "array", got "%s".', $key, get_debug_type( $value ) ) );
        }

        return $value;
    }

    /**
     * Returns the parameter keys.
     *
     * @return array An array of parameter keys
     */
    #[Pure] public function keys(): array {
        return array_keys( $this->parameters );
    }

    /**
     * Replaces the current parameters by a new set.
     *
     * @param array $parameters
     */
    public function replace( array $parameters = [] ): void {
        $this->parameters = $parameters;
    }

    /**
     * Adds parameters.
     *
     * @param array $parameters
     */
    public function add( array $parameters = [] ): void {
        $this->parameters = array_replace( $this->parameters, $parameters );
    }

    /**
     * Sets a parameter by name.
     *
     * @param string $key
     * @param mixed $value The value
     */
    public function set( string $key, $value ): void {
        $this->parameters[ $key ] = $value;
    }

    /**
     * Returns true if the parameter is defined.
     *
     * @param string $key
     *
     * @return bool true if the parameter exists, false otherwise
     */
    #[Pure] public function has( string $key ): bool {
        return \array_key_exists( $key, $this->parameters );
    }

    /**
     * Removes a parameter.
     *
     * @param string $key
     */
    public function remove( string $key ): void {
        unset( $this->parameters[ $key ] );
    }

    /**
     * Returns the alphabetic characters of the parameter value.
     *
     * @param string $key
     * @param string $default
     *
     * @return string The filtered value
     */
    public function getAlpha( string $key, string $default = '' ): string {
        return preg_replace( '/[^[:alpha:]]/', '', $this->get( $key, $default ) );
    }

    /**
     * Returns a parameter by name.
     *
     * @param string $key
     * @param mixed $default The default value if the parameter key does not exist
     *
     * @return mixed
     */
    #[Pure] public function get( string $key, $default = null ): mixed {
        return \array_key_exists( $key, $this->parameters ) ? $this->parameters[ $key ] : $default;
    }

    /**
     * Returns the alphabetic characters and digits of the parameter value.
     *
     * @param string $key
     * @param string $default
     *
     * @return string The filtered value
     */
    public function getAlnum( string $key, string $default = '' ): string {
        return preg_replace( '/[^[:alnum:]]/', '', $this->get( $key, $default ) );
    }

    /**
     * Returns the digits of the parameter value.
     *
     * @param string $key
     * @param string $default
     *
     * @return string The filtered value
     */
    public function getDigits( string $key, string $default = '' ): string {
        // we need to remove - and + because they're allowed in the filter
        return str_replace( [ '-', '+' ], '', $this->filter( $key, $default, \FILTER_SANITIZE_NUMBER_INT ) );
    }

    /**
     * Filter key.
     *
     * @param string $key
     * @param mixed $default Default = null
     * @param int $filter FILTER_* constant
     * @param mixed $options Filter options
     *
     * @return mixed
     * @see https://php.net/filter-var
     */
    public function filter( string $key, $default = null, int $filter = \FILTER_DEFAULT, $options = [] ): mixed {
        $value = $this->get( $key, $default );

        // Always turn $options into an array - this allows filter_var option shortcuts.
        if ( ! \is_array( $options ) && $options ) {
            $options = [ 'flags' => $options ];
        }

        // Add a convenience check for arrays.
        if ( \is_array( $value ) && ! isset( $options['flags'] ) ) {
            $options['flags'] = \FILTER_REQUIRE_ARRAY;
        }

        if ( ( \FILTER_CALLBACK & $filter ) && ! ( ( $options['options'] ?? null ) instanceof \Closure ) ) {
            trigger_deprecation( 'swift/http-foundation', '0.1', 'Not passing a Closure together with FILTER_CALLBACK to "%s()" is deprecated. Wrap your filter in a closure instead.', __METHOD__ );
            // throw new \InvalidArgumentException(sprintf('A Closure must be passed to "%s()" when FILTER_CALLBACK is used, "%s" given.', __METHOD__, get_debug_type($options['options'] ?? null)));
        }

        return filter_var( $value, $filter, $options );
    }

    /**
     * Returns the parameter value converted to integer.
     *
     * @param string $key
     * @param int $default
     *
     * @return int The filtered value
     */
    #[Pure] public function getInt( string $key, int $default = 0 ): int {
        return (int) $this->get( $key, $default );
    }

    /**
     * Returns the parameter value converted to boolean.
     *
     * @param string $key
     * @param bool $default
     *
     * @return bool The filtered value
     */
    public function getBoolean( string $key, bool $default = false ): bool {
        return $this->filter( $key, $default, \FILTER_VALIDATE_BOOLEAN );
    }

    /**
     * Returns an iterator for parameters.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator(): \ArrayIterator {
        return new \ArrayIterator( $this->parameters );
    }

    /**
     * Returns the number of parameters.
     *
     * @return int The number of parameters
     */
    #[Pure] public function count(): int {
        return \count( $this->parameters );
    }
}
