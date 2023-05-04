<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm;

use Cycle\ORM\EntityManagerInterface as CycleEntityManagerInterface;
use Cycle\ORM\EntityManager as CycleEntityManager;
use Cycle\ORM\Transaction\StateInterface;
use InvalidArgumentException;
use JetBrains\PhpStorm\Deprecated;
use stdClass;
use Swift\Dbal\Exceptions\NoResultException;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Orm\Dbal\QueryFactory;
use Swift\Orm\Dbal\ResultFactory;
use Swift\Dbal\Arguments\Arguments;
use Swift\Orm\Entity\EntityInterface;
use Swift\Orm\Mapping\ClassMetaDataFactory;

/**
 * @inheritDoc
 */
#[Autowire]
class EntityManager implements EntityManagerInterface {
    
    protected readonly CycleEntityManagerInterface $entityManager;
    
    public function __construct(
        private readonly QueryFactory         $queryFactory,
        private readonly ClassMetaDataFactory $classMetaDataFactory,
        private readonly Factory              $ormFactory,
        private readonly ResultFactory        $resultFactory,
    ) {
        $this->entityManager = new CycleEntityManager( $this->ormFactory->getOrm() );
    }
    
    /**
     * @inheritDoc
     */
    public function persist( EntityInterface $entity, bool $cascade = true ): \Swift\Orm\Dbal\EntityResultInterface {
        $this->entityManager->persist( $entity, $cascade );
        
        $this->resultFactory->createResult( $entity );
        
        return $entity;
    }
    
    /**
     * @inheritDoc
     */
    public function persistState( EntityInterface $entity, bool $cascade = true ): \Swift\Orm\Dbal\EntityResultInterface {
        $this->entityManager->persistState( $entity, $cascade );
        
        $this->resultFactory->createResult( $entity );
        
        return $entity;
    }
    
    /**
     * @inheritDoc
     */
    #[Deprecated( reason: 'Deprecated use-case. Use persist() instead.' )]
    public function save( string $entity, array|stdClass $state ): \Swift\Orm\Dbal\EntityResultInterface {
        throw new \RuntimeException( 'Deprecated usage' );
    }
    
    /**
     * @inheritDoc
     */
    public function findByPk( string $entity, int $pk ): \Swift\Orm\Dbal\EntityResultInterface|null {
        $result = $this->ormFactory->getOrm()->getRepository( $entity )->findByPK( $pk );
        
        if ( ! $result ) {
            return null;
        }
        
        return $this->resultFactory->createResult( $result );
    }
    
    /**
     * @inheritDoc
     */
    public function findOne( string $entity, array|stdClass $state = [], Arguments|null $arguments = null, bool $exceptionOnNotFound = false ): ?\Swift\Orm\Dbal\EntityResultInterface {
        $arguments ??= new Arguments();
        
        $arguments->setOffset( 0 );
        $arguments->setLimit( 1 );
        
        $result = $this->findMany( $entity, $state, $arguments, $exceptionOnNotFound );
        
        return $result[ 0 ] ?? null;
    }
    
    /**
     * @inheritDoc
     */
    public function findMany( string $entity, array|stdClass $state = [], Arguments|array|null $arguments = null, bool $exceptionOnNotFound = false ): \Swift\Orm\Dbal\ResultCollectionInterface {
        if ( is_array( $arguments ) ) {
            $arguments = Arguments::fromArray( $arguments );
        }
        $entityInstance = $this->getClassMetaDataFactory()->getClassMetaData( $entity )?->getEntity();
        $arguments      ??= new Arguments();
        
        if ( ! $entityInstance ) {
            throw new InvalidArgumentException( sprintf( 'Expected an instance of %s, instead got %s', EntityInterface::class, $entity ) );
        }
        
        $select = $this->queryFactory->getSelectQuery( $entity, $state, $arguments );
        
        $results = $select?->fetchAll();
        
        if ( $exceptionOnNotFound && empty( $results ) ) {
            throw new NoResultException( sprintf( 'No result found for search in %s', static::class ) );
        }
        
        if ( ! is_array( $results ) ) {
            $results = [ $results ];
        }
        
        return $this->resultFactory->createResultSet( $results, $select, $arguments );
    }
    
    /**
     * @inheritDoc
     */
    public function delete( EntityInterface $entity ): void {
        $this->entityManager->delete( $entity );
    }
    
    /**
     * @return \Swift\Orm\Mapping\ClassMetaDataFactory
     */
    private function getClassMetaDataFactory(): ClassMetaDataFactory {
        return $this->classMetaDataFactory;
    }
    
    /**
     * @inheritDoc
     *
     * @throws \Throwable
     */
    public function run( bool $throwException = true ): StateInterface {
        return $this->entityManager->run( $throwException );
    }
    
    public function clean( bool $cleanHeap = false ): static {
        $this->entityManager->clean( $cleanHeap );
        
        return $this;
    }
    
}