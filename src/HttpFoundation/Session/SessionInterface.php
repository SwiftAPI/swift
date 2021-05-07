<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Session;

use Swift\HttpFoundation\Session\Storage\MetadataBag;

/**
 * Interface for the session.
 *
 * @author Drak <drak@zikula.org>
 */
interface SessionInterface {
    /**
     * Starts the session storage.
     *
     * @return bool
     *
     * @throws \RuntimeException if session fails to start
     */
    public function start(): bool;

    /**
     * Returns the session ID.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Sets the session ID.
     *
     * @param string $id
     */
    public function setId( string $id ): void;

    /**
     * Returns the session name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Sets the session name.
     *
     * @param string $name
     */
    public function setName( string $name ): void;

    /**
     * Invalidates the current session.
     *
     * Clears all session attributes and flashes and regenerates the
     * session and deletes the old session from persistence.
     *
     * @param int|null $lifetime Sets the cookie lifetime for the session cookie. A null value
     *                      will leave the system settings unchanged, 0 sets the cookie
     *                      to expire with browser session. Time is in seconds, and is
     *                      not a Unix timestamp.
     *
     * @return bool
     */
    public function invalidate( int $lifetime = null ): bool;

    /**
     * Migrates the current session to a new session id while maintaining all
     * session attributes.
     *
     * @param bool $destroy Whether to delete the old session or leave it to garbage collection
     * @param int|null $lifetime Sets the cookie lifetime for the session cookie. A null value
     *                       will leave the system settings unchanged, 0 sets the cookie
     *                       to expire with browser session. Time is in seconds, and is
     *                       not a Unix timestamp.
     *
     * @return bool
     */
    public function migrate( bool $destroy = false, int $lifetime = null ): bool;

    /**
     * Force the session to be saved and closed.
     *
     * This method is generally not required for real sessions as
     * the session will be automatically saved at the end of
     * code execution.
     */
    public function save(): void;

    /**
     * Checks if an attribute is defined.
     *
     * @param string $name
     *
     * @return bool
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

    /**
     * Sets attributes.
     *
     * @param array $attributes
     */
    public function replace( array $attributes );

    /**
     * Removes an attribute.
     *
     * @param string $name
     *
     * @return mixed The removed value or null when it does not exist
     */
    public function remove( string $name ): mixed;

    /**
     * Clears all attributes.
     */
    public function clear(): void;

    /**
     * Checks if the session was started.
     *
     * @return bool
     */
    public function isStarted(): bool;

    /**
     * Registers a SessionBagInterface with the session.
     *
     * @param SessionBagInterface $bag
     */
    public function registerBag( SessionBagInterface $bag ): void;

    /**
     * Gets a bag instance by name.
     *
     * @param string $name
     */
    public function getBag( string $name );

    /**
     * Gets session meta.
     *
     * @return MetadataBag
     */
    public function getMetadataBag(): MetadataBag;
}
