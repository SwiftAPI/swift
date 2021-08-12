<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model;

use Dibi\Connection;
use Dibi\UniqueConstraintViolationException;
use InvalidArgumentException;
use stdClass;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\KernelDiTags;
use Swift\Model\Driver\DatabaseDriver;
use Swift\Model\Entity\Arguments;
use Swift\Model\Exceptions\DatabaseException;
use Swift\Model\Exceptions\DuplicateEntryException;
use Swift\Model\Exceptions\NoResultException;
use Swift\Model\Mapping\ClassMetaDataFactory;
use Swift\Model\Mapping\NamingStrategyInterface;
use Swift\Model\Query\QueryBuilder;
use Swift\Model\Query\QueryFactory;
use Swift\Model\Query\ResultFactory;
use Swift\Model\Types\TypeTransformer;

#[Autowire]
class EntityManager {

    /** @var \Swift\Model\EntityInterface[] */
    private array $entities;

    public function __construct(
        private DatabaseDriver          $driver,
        private QueryFactory            $queryFactory,
        private NamingStrategyInterface $namingStrategy,
        private ResultFactory           $resultFactory,
        private ClassMetaDataFactory    $classMetaDataFactory,
        private TypeTransformer         $typeTransformer,
    ) {
    }

    public function save( string $entity, array|stdClass $state ): \Swift\Model\Query\Result {
        $state     = (array) $state;
        $classMeta = $this->getClassMetaDataFactory()->getClassMetaData( $entity );

        // Check if record is new
        $isNew = empty( $state[ $classMeta->getTable()->getPrimaryKey()?->getPropertyName() ] );

        try {
            if ( $isNew ) {
                // Insert
                $insertQuery = $this->queryFactory->getInsertQuery( $entity, $state, $this );
                $insertId    = $insertQuery->execute( 'n' );

                return $this->findOne( $entity, [ $classMeta->getTable()->getPrimaryKey()->getPropertyName() => $insertId ] );
            }

            // Update
            $updateQuery = $this->queryFactory->getUpdateQuery( $entity, $state, $this );
            $updateQuery->execute();

            return $this->findOne( $entity, [ $classMeta->getTable()->getPrimaryKey()->getPropertyName() => $state[ $classMeta->getTable()->getPrimaryKey()->getPropertyName() ] ] );
        } catch ( UniqueConstraintViolationException $exception ) {
            throw new DuplicateEntryException( $exception->getMessage(), $exception->getCode() );
        } catch ( \Dibi\Exception $exception ) {
            throw new DatabaseException( $exception->getMessage(), $exception->getCode() );
        }
    }

    /**
     * @return \Swift\Model\Mapping\ClassMetaDataFactory
     */
    public function getClassMetaDataFactory(): ClassMetaDataFactory {
        return $this->classMetaDataFactory;
    }

    /**
     * Fetch a single row by the given state
     *
     * @param string         $entity
     * @param array|stdClass $state
     * @param bool           $exceptionOnNotFound
     *
     * @return Result|null
     */
    public function findOne( string $entity, array|stdClass $state, bool $exceptionOnNotFound = false ): ?\Swift\Model\Query\Result {
        $result = $this->findMany( $entity, $state, new Arguments( limit: 1 ), $exceptionOnNotFound );

        return $result[ 0 ] ?? null;
    }

    /**
     * Fetch all rows matching the given state and arguments
     *
     * @param string         $entity
     * @param array|stdClass $state
     * @param Arguments|null $arguments
     * @param bool           $exceptionOnNotFound
     *
     * @return \Swift\Model\Query\ResultSet
     */
    public function findMany( string $entity, array|stdClass $state, ?Arguments $arguments, bool $exceptionOnNotFound = false ): \Swift\Model\Query\ResultSet {
        $entityInstance = $this->getEntity( $entity );

        if ( ! $entityInstance ) {
            throw new InvalidArgumentException( sprintf( 'Expected an instance of %s, instead got %s', EntityInterface::class, $entity ) );
        }

        $query   = $this->queryFactory->getSelectQuery( $entity, (array) $state, $arguments, $this );
        $results = $query?->fetchAll() ?? [];

        if ( $exceptionOnNotFound && empty( $results ) ) {
            throw new NoResultException( sprintf( 'No result found for search in %s', static::class ) );
        }

        return $this->resultFactory->createResultSet( $results, $entityInstance, $query, $state, $arguments, $this );
    }

    /**
     * Get entity instance by name
     *
     * @param string $name
     *
     * @return \Swift\Model\EntityInterface|null
     */
    public function getEntity( string $name ): ?EntityInterface {
        return $this->entities[ $name ] ?? null;
    }

    /**
     * Method to delete a row from the database
     *
     * @param string         $entity
     * @param array|stdClass $state
     *
     * @return int  number of affected rows by deletion
     */
    public function delete( string $entity, array|stdClass $state ): int {
        try {
            $query = $this->queryFactory->getDeleteQuery( $entity, (array) $state, $this );

            return $query?->execute( 'a' ) ?? 0;
        } catch ( \Dibi\Exception $exception ) {
            throw new DatabaseException( $exception->getMessage(), $exception->getCode(), $exception );
        }
    }

    /**
     * @return \Swift\Model\Query\QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder {
        return new QueryBuilder( $this->getDriver() );
    }

    /**
     * @return \Swift\Model\Driver\DatabaseDriver
     */
    public function getDriver(): DatabaseDriver {
        return $this->driver;
    }

    /**
     * @return \Dibi\Connection
     */
    public function getConnection(): Connection {
        return $this->driver->getDriver()->getResource();
    }

    public function getQueryFactory(): QueryFactory {
        return $this->queryFactory;
    }

    public function getNamingStrategy(): NamingStrategyInterface {
        return $this->namingStrategy;
    }

    /**
     * @return \Swift\Model\Types\TypeTransformer
     */
    public function getTypeTransformer(): TypeTransformer {
        return $this->typeTransformer;
    }

    /**
     * @param \Swift\Model\EntityInterface[] $entities
     */
    #[Autowire]
    public function setEntities( #[Autowire( tag: KernelDiTags::ENTITY )] iterable $entities ): void {
        $this->entities = [];

        foreach ( $entities as $entity ) {
            $this->entities[ $entity::class ] = $entity;
        }
    }

}