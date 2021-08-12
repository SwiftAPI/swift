<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model;

use stdClass;
use Swift\Kernel\Attributes\DI;
use Swift\Kernel\KernelDiTags;
use Swift\Model\Entity\Arguments;
use Swift\Model\Exceptions\DatabaseException;

/**
 * Interface EntityInterface
 * @package Swift\Model
 */
#[DI(tags: [KernelDiTags::ENTITY])]
interface EntityInterface {

    /**
     * Fetch a single row by the given state
     *
     * @param array|stdClass $state
     * @param bool           $exceptionOnNotFound
     *
     * @return \Swift\Model\Query\Result|null
     */
    public function findOne( array|stdClass $state, bool $exceptionOnNotFound = false ): \Swift\Model\Query\Result|null;

    /**
     * Fetch all rows matching given state and arguments
     *
     * @param array|stdClass $state
     * @param Arguments|null $arguments
     * @param bool           $exceptionOnNotFound
     *
     * @return \Swift\Model\Query\ResultSet
     */
    public function findMany( array|stdClass $state, Arguments|null $arguments = null, bool $exceptionOnNotFound = false ): \Swift\Model\Query\ResultSet;

    /**
     * Save/update based on the given state
     *
     * @param array|stdClass $state
     *
     * @return \Swift\Model\Query\Result Return updated/created result from action
     *
     * @throws \Swift\Model\Exceptions\DatabaseException
     */
    public function save( array|stdClass $state ): \Swift\Model\Query\Result;

    /**
     * Delete row where primary key value equals given key
     *
     * @param array $state
     *
     * @return int
     *
     * @throws \Swift\Model\Exceptions\DatabaseException
     */
    public function delete( array $state ): int;



}