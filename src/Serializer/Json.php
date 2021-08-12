<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Serializer;

use RuntimeException;

/**
 * Class Json
 * @package Swift\Serializer
 */
class Json implements SerializerInterface {

    /**
     * How objects should be encoded: as arrays or as stdClass.
     *
     * TYPE_ARRAY is 1, which also conveniently evaluates to a boolean true
     * value, allowing it to be used with ext/json's functions.
     */
    public const TYPE_ARRAY = 1;
    public const TYPE_OBJECT = 0;

    private int $mode = self::TYPE_ARRAY;
    private bool $prettyPrint = false;

    /**
     * Json constructor.
     */
    public function __construct(
        private mixed $value,
    ) {
    }

    public function modeObject(): static {
        $this->mode = self::TYPE_OBJECT;

        return $this;
    }

    public function modeArray(): static {
        $this->mode = self::TYPE_ARRAY;

        return $this;
    }

    public function prettyPrint( bool $state ): static {
        $this->prettyPrint = $state;

        return $this;
    }


    public function serialize( mixed $value = null ): string {
        $value ??= $this->value;
        if ( ! function_exists( 'json_encode' ) ) {
            throw new RuntimeException('Decoding function "json_encode" is missing!');
        }

        $encodeOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;

        if ( $this->prettyPrint ) {
            $encodeOptions |= JSON_PRETTY_PRINT;
        }

        return json_encode( $value, $encodeOptions );
    }

    public function unSerialize( string $value = null ): mixed {
        $value ??= $this->value;
        $decoded = json_decode( $value, (bool) $this->mode );

        switch ( json_last_error() ) {
            case JSON_ERROR_NONE:
                if (!is_array($decoded) && !is_object($decoded) && empty($decoded)) {
                    return null;
                }
                return $decoded;
            case JSON_ERROR_DEPTH:
                throw new RuntimeException( 'Decoding failed: Maximum stack depth exceeded' );
            case JSON_ERROR_CTRL_CHAR:
                throw new RuntimeException( 'Decoding failed: Unexpected control character found' );
            case JSON_ERROR_SYNTAX:
                throw new RuntimeException( 'Decoding failed: Syntax error' );
            default:
                throw new RuntimeException( 'Decoding failed' );
        }
    }
}