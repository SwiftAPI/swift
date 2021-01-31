<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation;

// Help opcache.preload discover always-needed symbols
use Swift\Kernel\Attributes\DI;

class_exists( AcceptHeaderItem::class );

/**
 * Represents an Accept-* header.
 *
 * An accept header is compound with a list of items,
 * sorted by descending quality.
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
#[DI( exclude: true, autowire: false )]
class AcceptHeader {
    /**
     * @var AcceptHeaderItem[]
     */
    private array $items = [];

    /**
     * @var bool
     */
    private bool $sorted = true;

    /**
     * @param AcceptHeaderItem[] $items
     */
    public function __construct( array $items ) {
        foreach ( $items as $item ) {
            $this->add( $item );
        }
    }

    /**
     * Adds an item.
     *
     * @return $this
     */
    public function add( AcceptHeaderItem $item ) {
        $this->items[ $item->getValue() ] = $item;
        $this->sorted                     = false;

        return $this;
    }

    /**
     * Builds an AcceptHeader instance from a string.
     *
     * @param string|null $headerValue
     *
     * @return static
     */
    public static function fromString( ?string $headerValue ): static {
        $index = 0;

        $parts = HeaderUtils::split( $headerValue ?? '', ',;=' );

        return new self( array_map( static function ( $subParts ) use ( &$index ) {
            $part       = array_shift( $subParts );
            $attributes = HeaderUtils::combine( $subParts );

            $item = new AcceptHeaderItem( $part[0], $attributes );
            $item->setIndex( $index ++ );

            return $item;
        }, $parts ) );
    }

    /**
     * Returns header value's string representation.
     *
     * @return string
     */
    public function __toString() {
        return implode( ',', $this->items );
    }

    /**
     * Tests if header has given value.
     *
     * @param string $value
     *
     * @return bool
     */
    public function has( string $value ): bool {
        return isset( $this->items[ $value ] );
    }

    /**
     * Returns given value's item, if exists.
     *
     * @param string $value
     *
     * @return AcceptHeaderItem|null
     */
    public function get( string $value ): static|null {
        return $this->items[ $value ] ?? $this->items[ explode( '/', $value )[0] . '/*' ] ?? $this->items['*/*'] ?? $this->items['*'] ?? null;
    }

    /**
     * Returns all items.
     *
     * @return AcceptHeaderItem[]
     */
    public function all(): array {
        $this->sort();

        return $this->items;
    }

    /**
     * Sorts items by descending quality.
     */
    private function sort(): void {
        if ( ! $this->sorted ) {
            uasort( $this->items, static function ( AcceptHeaderItem $a, AcceptHeaderItem $b ) {
                $qA = $a->getQuality();
                $qB = $b->getQuality();

                if ( $qA === $qB ) {
                    return $a->getIndex() > $b->getIndex() ? 1 : - 1;
                }

                return $qA > $qB ? - 1 : 1;
            } );

            $this->sorted = true;
        }
    }

    /**
     * Filters items on their value using given regex.
     *
     * @param string $pattern
     *
     * @return static
     */
    public function filter( string $pattern ): static {
        return new self( array_filter( $this->items, static function ( AcceptHeaderItem $item ) use ( $pattern ) {
            return preg_match( $pattern, $item->getValue() );
        } ) );
    }

    /**
     * Returns first item.
     *
     * @return AcceptHeaderItem|null
     */
    public function first(): ?AcceptHeaderItem {
        $this->sort();

        return ! empty( $this->items ) ? reset( $this->items ) : null;
    }
}
