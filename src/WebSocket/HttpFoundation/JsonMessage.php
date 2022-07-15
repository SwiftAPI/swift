<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\WebSocket\HttpFoundation;

use Swift\DependencyInjection\Attributes\DI;
use TypeError;

/**
 * Response represents a websocket message in JSON format.
 *
 * Note that this class does not force the returned JSON content to be an
 * object. It is however recommended that you do return an object as it
 * protects yourself against XSSI and JSON-JavaScript Hijacking.
 */
#[DI( exclude: true, autowire: false )]
class JsonMessage extends Message {
    
    public const DEFAULT_ENCODING_OPTIONS = 15;
    protected mixed $data;
    
    // Encode <, >, ', &, and " characters in the JSON, making it also safe to be embedded into HTML.
    // 15 === JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    protected string|int|null $callback = null;
    protected int $encodingOptions = self::DEFAULT_ENCODING_OPTIONS;
    
    /**
     * @param mixed $data The response data
     * @param bool  $json If the data is already a JSON string
     *
     * @throws \JsonException
     */
    public function __construct( $data = null, bool $json = false ) {
        if ( $json && ! \is_string( $data ) && ! is_numeric( $data ) && ! \is_callable( [ $data, '__toString' ] ) ) {
            throw new TypeError( sprintf( '"%s": If $json is set to true, argument $data must be a string or object implementing __toString(), "%s" given.', __METHOD__, get_debug_type( $data ) ) );
        }
        
        if ( null === $data ) {
            $data = new \ArrayObject();
        }
        
        $content = $json ? $data : $this->toJson( $data );
        
        parent::__construct( $content );
    }
    
    /**
     * Sets the data to be sent as JSON.
     *
     * @param mixed $data
     *
     * @return string
     *
     * @throws \InvalidArgumentException|\JsonException
     */
    private function toJson( $data = [] ): string {
        try {
            $data = json_encode( $data, JSON_THROW_ON_ERROR | $this->encodingOptions );
        } catch ( \JsonException $e ) {
            if ( 'Exception' === \get_class( $e ) && str_starts_with( $e->getMessage(), 'Failed calling ' ) ) {
                throw $e->getPrevious() ?: $e;
            }
            throw $e;
        }
        
        if ( \PHP_VERSION_ID >= 70300 && ( \JSON_THROW_ON_ERROR & $this->encodingOptions ) ) {
            return $data;
        }
        
        return $data;
    }
    
    /**
     * Factory method for chainability.
     *
     * Example:
     *
     *     return JsonResponse::fromJsonString('{"key": "value"}')
     *         ->setSharedMaxAge(300);
     *
     * @param string $data The JSON response string
     *
     * @return static
     * @throws \JsonException
     */
    public static function fromJsonString( string $data ): static {
        return new static( $data, true );
    }
    
    /**
     * Sets the JSONP callback.
     *
     * @param string|null $callback The JSONP callback or null to use none
     *
     * @return $this
     *
     * @throws \InvalidArgumentException When the callback name is not valid
     */
    public function setCallback( string $callback = null ): static {
        if ( null !== $callback ) {
            // partially taken from https://geekality.net/2011/08/03/valid-javascript-identifier/
            // partially taken from https://github.com/willdurand/JsonpCallbackValidator
            //      JsonpCallbackValidator is released under the MIT License. See https://github.com/willdurand/JsonpCallbackValidator/blob/v1.1.0/LICENSE for details.
            //      (c) William Durand <william.durand1@gmail.com>
            $pattern  = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*(?:\[(?:"(?:\\\.|[^"\\\])*"|\'(?:\\\.|[^\'\\\])*\'|\d+)\])*?$/u';
            $reserved = [
                'break',
                'do',
                'instanceof',
                'typeof',
                'case',
                'else',
                'new',
                'var',
                'catch',
                'finally',
                'return',
                'void',
                'continue',
                'for',
                'switch',
                'while',
                'debugger',
                'function',
                'this',
                'with',
                'default',
                'if',
                'throw',
                'delete',
                'in',
                'try',
                'class',
                'enum',
                'extends',
                'super',
                'const',
                'export',
                'import',
                'implements',
                'let',
                'private',
                'public',
                'yield',
                'interface',
                'package',
                'protected',
                'static',
                'null',
                'true',
                'false',
            ];
            $parts    = explode( '.', $callback );
            foreach ( $parts as $part ) {
                if ( ! preg_match( $pattern, $part ) || \in_array( $part, $reserved, true ) ) {
                    throw new \InvalidArgumentException( 'The callback name is not valid.' );
                }
            }
        }
        
        $this->callback = $callback;
        
        return $this;
    }
    
    /**
     * Returns options used while encoding data to JSON.
     *
     * @return int
     */
    public function getEncodingOptions(): int {
        return $this->encodingOptions;
    }
    
    /**
     * Sets options used while encoding data to JSON.
     *
     * @param int $encodingOptions
     *
     * @return static
     */
    public function setEncodingOptions( int $encodingOptions ): static {
        $new                  = clone $this;
        $new->encodingOptions = $encodingOptions;
        
        return $new->withBody( Stream::create( ( new \Swift\Serializer\Json( $new->data ) )->serialize() ) );
    }
    
}
