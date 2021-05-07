<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Session\Flash;

use JetBrains\PhpStorm\Pure;

/**
 * FlashBag flash message container.
 *
 * @author Drak <drak@zikula.org>
 */
class FlashBag implements FlashBagInterface {

    private string $name = 'flashes';
    private array $flashes = [];
    private string $storageKey;

    /**
     * @param string $storageKey The key used to store flashes in the session
     */
    public function __construct( string $storageKey = '_swift_flashes' ) {
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
    public function initialize( array &$flashes ): void {
        $this->flashes = &$flashes;
    }

    /**
     * {@inheritdoc}
     */
    public function add( string $type, $message ): void {
        $this->flashes[ $type ][] = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function peek( string $type, array $default = [] ): mixed {
        return $this->has( $type ) ? $this->flashes[ $type ] : $default;
    }

    /**
     * {@inheritdoc}
     */
    #[Pure] public function has( string $type ): bool {
        return \array_key_exists( $type, $this->flashes ) && $this->flashes[ $type ];
    }

    /**
     * {@inheritdoc}
     */
    public function get( string $type, array $default = [] ): mixed {
        if ( ! $this->has( $type ) ) {
            return $default;
        }

        $return = $this->flashes[ $type ];

        unset( $this->flashes[ $type ] );

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function set( string $type, $messages ): void {
        $this->flashes[ $type ] = (array) $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function setAll( array $messages ): void {
        $this->flashes = $messages;
    }

    /**
     * {@inheritdoc}
     */
    #[Pure] public function keys(): array {
        return array_keys( $this->flashes );
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
    public function clear(): mixed {
        return $this->all();
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array {
        $return        = $this->peekAll();
        $this->flashes = [];

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function peekAll(): array {
        return $this->flashes;
    }
}
