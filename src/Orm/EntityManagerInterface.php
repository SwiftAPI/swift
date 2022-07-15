<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm;

use Cycle\ORM\Transaction\StateInterface;
use JetBrains\PhpStorm\Deprecated;
use stdClass;
use \Swift\Dbal\Arguments\Arguments;
use Swift\Orm\Entity\EntityInterface;

/**
 * Interface EntityInterface
 * @package Swift\Orm
 */
interface EntityManagerInterface {
    
    /**
     * Fetch entity by primary key. This can load managed entities directly from the unit of work, which makes for a performance improvement
     *
     * @template T
     *
     * @param class-string<T> $entity
     * @param int             $pk
     *
     * @return T|null
     */
    public function findByPk( string $entity, int $pk ): \Swift\Orm\Dbal\EntityResultInterface|null;
    
    /**
     * Fetch a single row by the given state
     *
     * @template T
     *
     * @param class-string<T>                  $entity
     * @param array|stdClass                   $state
     * @param \Swift\Dbal\Arguments\Arguments|null $arguments
     * @param bool                             $exceptionOnNotFound
     *
     * @return T|null
     */
    public function findOne( string $entity, array|stdClass $state = [], Arguments|null $arguments = null, bool $exceptionOnNotFound = false ): \Swift\Orm\Dbal\EntityResultInterface|null;
    
    /**
     * Fetch all rows matching given state and arguments
     *
     * @template T
     *
     * @param class-string<T>                        $entity
     * @param array|stdClass                         $state
     * @param \Swift\Orm\Entity\Arguments|\Swift\Dbal\Arguments\ArgumentInterface[]|null $arguments
     * @param bool                                   $exceptionOnNotFound
     *
     * @return \Swift\Orm\Dbal\ResultCollectionInterface<T>
     */
    public function findMany( string $entity, array|stdClass $state, Arguments|array|null $arguments = null, bool $exceptionOnNotFound = false ): \Swift\Orm\Dbal\ResultCollectionInterface;
    
    /**
     * Save/update based on the given state
     *
     * @template T
     *
     * @param class-string<T> $entity
     * @param array|stdClass  $state
     *
     * @return T Return updated/created result from action
     *
     * @throws \Swift\Dbal\Exceptions\DatabaseException
     */
    #[Deprecated( reason: 'Deprecated use-case. Use persist() instead.' )]
    public function save( string $entity, array|stdClass $state ): \Swift\Orm\Dbal\EntityResultInterface;
    
    /**
     * Tells the EntityManager to make an Entity managed and persistent with deferred state syncing.
     *
     * Entity will be queued up without fixing current state.
     * Entity state changes will be synced with queued state during run operation.
     *
     * Note: The entity will be updated or inserted into the database at transaction
     * run or as a result of the run operation.
     *
     * @template T of \Swift\Orm\Entity\EntityInterface
     *
     * @param T $entity
     *
     * @return T
     */
    public function persist( EntityInterface $entity, bool $cascade = true ): \Swift\Orm\Dbal\EntityResultInterface;
    
    /**
     * Tells the EntityManager to make an Entity managed and persistent.
     *
     * Entity will be queued up with fixing current state.
     * Entity state changes after adding to the queue will be ignored.
     *
     * Note: The entity will be updated or inserted into the database at transaction
     * run or as a result of the run operation.
     *
     * @template T of \Swift\Orm\Entity\EntityInterface
     *
     * @param T $entity
     *
     * @return T
     */
    public function persistState( EntityInterface $entity, bool $cascade = true ): \Swift\Orm\Dbal\EntityResultInterface;
    
    /**
     * Delete row where primary key value equals given key
     *
     * @param EntityInterface $entity
     *
     * @return void
     *
     * @throws \Swift\Dbal\Exceptions\DatabaseException
     */
    public function delete( EntityInterface $entity ): void;
    
    /**
     * Sync all changes to entities that have been added to the queue with database.
     *
     * Synchronizes the in-memory state of managed entities with the database.
     */
    public function run( bool $throwException = true ): StateInterface;
    
    /**
     * Clean state.
     */
    public function clean(bool $cleanHeap = false): static;
    
    
}