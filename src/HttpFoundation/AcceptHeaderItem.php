<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation;



use Swift\DependencyInjection\Attributes\DI;

/**
 * Represents an Accept-* header item.
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
#[DI( exclude: true, autowire: false )]
class AcceptHeaderItem {

    private $value;
    private $quality = 1.0;
    private $index = 0;
    private $attributes = [];

    public function __construct( string $value, array $attributes = [] ) {
        $this->value = $value;
        foreach ( $attributes as $name => $value ) {
            $this->setAttribute( $name, $value );
        }
    }

    /**
     * Set an attribute.
     *
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function setAttribute( string $name, string $value ): static {
        if ( 'q' === $name ) {
            $this->quality = (float) $value;
        } else {
            $this->attributes[ $name ] = $value;
        }

        return $this;
    }

    /**
     * Builds an AcceptHeaderInstance instance from a string.
     *
     * @param string|null $itemValue
     *
     * @return self
     */
    public static function fromString( ?string $itemValue ): static {
        $parts = HeaderUtils::split( $itemValue ?? '', ';=' );

        $part       = array_shift( $parts );
        $attributes = HeaderUtils::combine( $parts );

        return new self( $part[0], $attributes );
    }

    /**
     * Returns header value's string representation.
     *
     * @return string
     */
    public function __toString(): string {
        $string = $this->value . ( $this->quality < 1 ? ';q=' . $this->quality : '' );
        if ( \count( $this->attributes ) > 0 ) {
            $string .= '; ' . HeaderUtils::toString( $this->attributes, ';' );
        }

        return $string;
    }

    /**
     * Returns the item value.
     *
     * @return string
     */
    public function getValue(): string {
        return $this->value;
    }

    /**
     * Set the item value.
     *
     * @param string $value
     *
     * @return $this
     */
    public function setValue( string $value ): static {
        $this->value = $value;

        return $this;
    }

    /**
     * Returns the item quality.
     *
     * @return float
     */
    public function getQuality(): float {
        return $this->quality;
    }

    /**
     * Set the item quality.
     *
     * @param float $quality
     *
     * @return $this
     */
    public function setQuality( float $quality ): static {
        $this->quality = $quality;

        return $this;
    }

    /**
     * Returns the item index.
     *
     * @return int
     */
    public function getIndex(): int {
        return $this->index;
    }

    /**
     * Set the item index.
     *
     * @param int $index
     *
     * @return $this
     */
    public function setIndex( int $index ): static {
        $this->index = $index;

        return $this;
    }

    /**
     * Tests if an attribute exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasAttribute( string $name ): bool {
        return isset( $this->attributes[ $name ] );
    }

    /**
     * Returns an attribute by its name.
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getAttribute( string $name, $default = null ): mixed {
        return $this->attributes[ $name ] ?? $default;
    }

    /**
     * Returns all attributes.
     *
     * @return array
     */
    public function getAttributes(): array {
        return $this->attributes;
    }
}
