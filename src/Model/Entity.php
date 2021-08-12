<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model;

use stdClass;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Attributes\DI;
use Swift\Kernel\KernelDiTags;
use Swift\Model\Entity\Arguments;

/**
 * Class Entity
 * @package Swift\Model\Entity
 */
#[DI( tags: [ KernelDiTags::ENTITY ] ), Autowire]
abstract class Entity implements EntityInterface {

    protected EntityManager $entityManager;

    /**
     * Method to save/update based on the current state
     *
     * @param array|stdClass $state
     *
     * @return \Swift\Model\Query\Result
     */
    public function save( array|stdClass $state ): \Swift\Model\Query\Result {
        return $this->entityManager->save( static::class, $state );
    }

    /**
     * Fetch a single row by the given state
     *
     * @param array|stdClass $state
     * @param bool $exceptionOnNotFound
     *
     * @return stdClass|null
     */
    public function findOne( array|stdClass $state, bool $exceptionOnNotFound = false ): \Swift\Model\Query\Result|null {
        return $this->entityManager->findOne( static::class, $state, $exceptionOnNotFound );
    }

    /**
     * Fetch all rows matching the given state and arguments
     *
     * @param array|stdClass $state
     * @param Arguments|null $arguments
     * @param bool $exceptionOnNotFound
     *
     * @return \Swift\Model\Query\ResultSet
     */
    public function findMany( array|stdClass $state, Arguments|null $arguments = null, bool $exceptionOnNotFound = false ): \Swift\Model\Query\ResultSet {
        return $this->entityManager->findMany( static::class, $state, $arguments, $exceptionOnNotFound );
    }

    /**
     * Method to delete a row from the database
     *
     * @param array|\stdClass $state
     *
     * @return int  number of affected rows by deletion
     */
    public function delete( array|stdClass $state ): int {
        return $this->entityManager->delete( static::class, $state );
    }


    #[Autowire]
    public function setEntityManager( EntityManager $entityManager ): void {
        $this->entityManager = $entityManager;
    }


}