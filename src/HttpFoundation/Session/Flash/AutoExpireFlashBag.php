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
 * AutoExpireFlashBag flash message container.
 *
 * @author Drak <drak@zikula.org>
 */
class AutoExpireFlashBag implements FlashBagInterface {

    private string $name = 'flashes';
    private array $flashes = [ 'display' => [], 'new' => [] ];
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

        // The logic: messages from the last request will be stored in new, so we move them to previous
        // This request we will show what is in 'display'.  What is placed into 'new' this time round will
        // be moved to display next time round.
        $this->flashes['display'] = \array_key_exists( 'new', $this->flashes ) ? $this->flashes['new'] : [];
        $this->flashes['new']     = [];
    }

    /**
     * {@inheritdoc}
     */
    public function add( string $type, $message ): void {
        $this->flashes['new'][ $type ][] = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function peek( string $type, array $default = [] ) {
        return $this->has( $type ) ? $this->flashes['display'][ $type ] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function has( string $type ): bool {
        return \array_key_exists( $type, $this->flashes['display'] ) && $this->flashes['display'][ $type ];
    }

    /**
     * {@inheritdoc}
     */
    #[Pure] public function peekAll(): array {
        return \array_key_exists( 'display', $this->flashes ) ? (array) $this->flashes['display'] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function get( string $type, array $default = [] ) {
        $return = $default;

        if ( ! $this->has( $type ) ) {
            return $return;
        }

        if ( isset( $this->flashes['display'][ $type ] ) ) {
            $return = $this->flashes['display'][ $type ];
            unset( $this->flashes['display'][ $type ] );
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function setAll( array $messages ): void {
        $this->flashes['new'] = $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function set( string $type, $messages ): void {
        $this->flashes['new'][ $type ] = (array) $messages;
    }

    /**
     * {@inheritdoc}
     */
    #[Pure] public function keys(): array {
        return array_keys( $this->flashes['display'] );
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
    public function all(): mixed {
        $return                   = $this->flashes['display'];
        $this->flashes['display'] = [];

        return $return;
    }
}
