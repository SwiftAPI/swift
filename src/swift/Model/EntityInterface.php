<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model;

use stdClass;
use Swift\Kernel\Attributes\DI;
use Swift\Kernel\DiTags;
use Swift\Model\Entity\Arguments;
use Swift\Model\Exceptions\DatabaseException;

/**
 * Interface EntityInterface
 * @package Swift\Model
 */
#[DI(tags: [DiTags::ENTITY])]
interface EntityInterface {

    /**
     * Fetch a single row by the given state
     *
     * @param array|stdClass $state
     * @param bool $exceptionOnNotFound
     *
     * @return Result|null
     */
    public function findOne( array|stdClass $state, bool $exceptionOnNotFound = false ): ?Result;

    /**
     * Fetch all rows matching given state and arguments
     *
     * @param array|stdClass $state
     * @param Arguments|null $arguments
     * @param bool $exceptionOnNotFound
     *
     * @return ResultSet
     */
    public function findMany( array|stdClass $state, Arguments|null $arguments = null, bool $exceptionOnNotFound = false ): ResultSet;

    /**
     * Save/update based on the given state
     *
     * @param array|stdClass $state
     *
     * @return Result Return updated/created result from action
     *
     * @throws DatabaseException
     */
    public function save( array|stdClass $state ): Result;

    /**
     * Delete row where primary key value equals given key
     *
     * @param mixed $key
     *
     * @return int
     *
     * @throws DatabaseException
     */
    public function delete( mixed $key ): int;

    /**
     * Get primary key name
     *
     * @return string|null
     */
    public function getPrimaryKey(): string|null;



}