<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Session;

/**
 * Session Bag store.
 *
 * @author Drak <drak@zikula.org>
 */
interface SessionBagInterface {

    /**
     * Gets this bag's name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Initializes the Bag.
     *
     * @param array $array
     */
    public function initialize( array &$array );

    /**
     * Gets the storage key for this bag.
     *
     * @return string
     */
    public function getStorageKey(): string;

    /**
     * Clears out data from bag.
     *
     * @return mixed Whatever data was contained
     */
    public function clear(): mixed;
}
