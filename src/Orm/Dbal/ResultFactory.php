<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Dbal;

use stdClass;
use Swift\Dbal\Arguments\Arguments;
use Swift\Dbal\QueryBuilder;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Dbal\Arguments\Where;
use Swift\Orm\Collection\ArrayCollection;
use Swift\Orm\Collection\ArrayCollectionInterface;
use Swift\Orm\Entity\EntityInterface;
use Swift\Orm\EntityManager;
use Swift\Orm\Mapping\ClassMetaDataFactory;
use Swift\Orm\Mapping\Definition\Relation\EntityRelationType;
use Swift\Orm\Orm;
use Swift\Orm\Utilities\Callback;

#[Autowire]
class ResultFactory {
    
    public function __construct(
        protected readonly ClassMetaDataFactory $classMetaDataFactory,
    ) {
    }
    
    /**
     * @template T
     *
     * @param array                            $results
     * @param \Cycle\ORM\Select|null           $queryBuilder
     * @param \Swift\Dbal\Arguments\Arguments|null $arguments
     *
     * @return \Swift\Orm\Dbal\ResultCollectionInterface<T>
     */
    public function createResultSet( array $results, ?\Cycle\ORM\Select $queryBuilder, ?Arguments $arguments ): ResultCollectionInterface {
        $resultSet = (new ResultCollection())->initialize(
            Callback::createCallbackForPayload( $queryBuilder ),
            Callback::createCallbackForPayload( $arguments )
        );
    
        foreach ( $results as $result ) {
            $resultSet[] = $this->createResult( $result );
        }
    
        return $resultSet;
    }
    
    public function createResult( EntityInterface $entity ): EntityInterface&EntityResultInterface {
        $item   = $entity->initialize(
            Callback::createCallbackForPayload( $this->classMetaDataFactory->getClassMetaData( $entity->_getName() ) ),
        );
        
//        foreach ( $result as $key => $value ) {
//            $field = $this->orm->getClassMetaDataFactory()->getClassMetaData( $entity )->getEntity()->getFieldByDatabaseName( $key );
//
//            if ( ! $field ) {
//                continue;
//            }
//
//            $item->{$field->getPropertyName()} = $this->orm->getTypeTransformer()->transformToPhpValue( $field->getType()->getName(), $value, $entity, $field );
//        }
        
        return $item;
    }
    
}