<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration\Tree;

use Swift\Configuration\Exception\UnknownConfigurationKeyException;

/**
 * Interface TreeInterface
 * @package Swift\Configuration\Tree
 */
interface TreeInterface {

    /**
     * @param string $identifier
     *
     * @return mixed
     *
     * @throws UnknownConfigurationKeyException
     */
    public function get( string $identifier ): mixed;

    /**
     * @param string $identifier
     * @param mixed $value
     *
     * @throws UnknownConfigurationKeyException
     */
    public function set( string $identifier, mixed $value ): void;

    /**
     * Determine whether tree contains a given identifier
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function has( string $identifier ): bool;

    /**
     * Configs to array
     *
     * @return array
     */
    public function toArray(): array;

}