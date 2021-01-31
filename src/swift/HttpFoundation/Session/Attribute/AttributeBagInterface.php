<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Session\Attribute;

use Swift\HttpFoundation\Session\SessionBagInterface;

/**
 * Attributes store.
 *
 * @author Drak <drak@zikula.org>
 */
interface AttributeBagInterface extends SessionBagInterface {

    /**
     * Checks if an attribute is defined.
     *
     * @param string $name
     *
     * @return bool true if the attribute is defined, false otherwise
     */
    public function has( string $name ): bool;

    /**
     * Returns an attribute.
     *
     * @param string $name
     * @param mixed $default The default value if not found
     *
     * @return mixed
     */
    public function get( string $name, $default = null ): mixed;

    /**
     * Sets an attribute.
     *
     * @param string $name
     * @param mixed $value
     */
    public function set( string $name, mixed $value );

    /**
     * Returns attributes.
     *
     * @return array
     */
    public function all(): array;

    public function replace( array $attributes );

    /**
     * Removes an attribute.
     *
     * @param string $name
     *
     * @return mixed The removed value or null when it does not exist
     */
    public function remove( string $name ): mixed;
}
