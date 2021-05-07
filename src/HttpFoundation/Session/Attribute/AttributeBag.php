<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Session\Attribute;

use JetBrains\PhpStorm\Pure;

/**
 * This class relates to session attribute storage.
 */
class AttributeBag implements AttributeBagInterface, \IteratorAggregate, \Countable {

    protected array $attributes = [];
    private string $name = 'attributes';
    private $storageKey;

    /**
     * @param string $storageKey The key used to store attributes in the session
     */
    public function __construct( string $storageKey = '_sf2_attributes' ) {
        $this->storageKey = $storageKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string {
        return $this->name;
    }

    public function setName( string $name ): void {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize( array &$attributes ): void {
        $this->attributes = &$attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorageKey(): string {
        return $this->storageKey;
    }

    /**
     * {@inheritdoc}
     */
    #[Pure] public function has( string $name ): bool {
        return \array_key_exists( $name, $this->attributes );
    }

    /**
     * {@inheritdoc}
     */
    #[Pure] public function get( string $name, $default = null ): mixed {
        return \array_key_exists( $name, $this->attributes ) ? $this->attributes[ $name ] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function replace( array $attributes ): void {
        $this->attributes = [];
        foreach ( $attributes as $key => $value ) {
            $this->set( $key, $value );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set( string $name, mixed $value ): void {
        $this->attributes[ $name ] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function remove( string $name ): mixed {
        $retval = null;
        if ( \array_key_exists( $name, $this->attributes ) ) {
            $retval = $this->attributes[ $name ];
            unset( $this->attributes[ $name ] );
        }

        return $retval;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): array {
        $return           = $this->attributes;
        $this->attributes = [];

        return $return;
    }

    /**
     * Returns an iterator for attributes.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator(): \ArrayIterator {
        return new \ArrayIterator( $this->attributes );
    }

    /**
     * Returns the number of attributes.
     *
     * @return int The number of attributes
     */
    #[Pure] public function count(): int {
        return \count( $this->attributes );
    }
}
