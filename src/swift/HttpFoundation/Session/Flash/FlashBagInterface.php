<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Session\Flash;

use Swift\HttpFoundation\Session\SessionBagInterface;

/**
 * FlashBagInterface.
 *
 * @author Drak <drak@zikula.org>
 */
interface FlashBagInterface extends SessionBagInterface
{
    /**
     * Adds a flash message for the given type.
     *
     * @param mixed $message
     */
    public function add(string $type, $message);

    /**
     * Registers one or more messages for a given type.
     *
     * @param string|array $messages
     */
    public function set(string $type, $messages);

    /**
     * Gets flash messages for a given type.
     *
     * @param string $type    Message category type
     * @param array  $default Default value if $type does not exist
     *
     * @return array
     */
    public function peek(string $type, array $default = []);

    /**
     * Gets all flash messages.
     *
     * @return array
     */
    public function peekAll();

    /**
     * Gets and clears flash from the stack.
     *
     * @param array $default Default value if $type does not exist
     *
     * @return array
     */
    public function get(string $type, array $default = []);

    /**
     * Gets and clears flashes from the stack.
     *
     * @return array
     */
    public function all();

    /**
     * Sets all flash messages.
     */
    public function setAll(array $messages);

    /**
     * Has flash messages for a given type?
     *
     * @return bool
     */
    public function has(string $type);

    /**
     * Returns a list of all defined types.
     *
     * @return array
     */
    public function keys();
}
