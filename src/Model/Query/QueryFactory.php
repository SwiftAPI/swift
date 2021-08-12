<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Query;

use Swift\Kernel\Attributes\Autowire;
use Swift\Model\Arguments\Arguments;
use Swift\Model\EntityManager;
use Swift\Model\Mapping\ClassMetaData;
use Swift\Model\Mapping\ClassMetaDataFactory;
use Swift\Model\Mapping\Field;
use Swift\Model\Types\TypeTransformer;

#[Autowire]
class QueryFactory {

    public function __construct(
        private ClassMetaDataFactory $classMetaDataFactory,
        private TypeTransformer      $typeTransformer,
    ) {
    }

    /**
     * @param string                                $entity
     * @param array                                 $state
     * @param \Swift\Model\Arguments\Arguments|null $arguments
     * @param \Swift\Model\EntityManager            $entityManager
     *
     * @return QueryBuilder|null
     */
    public function getSelectQuery( string $entity, array $state, ?Arguments $arguments, EntityManager $entityManager ): ?QueryBuilder {
        $classMeta = $this->classMetaDataFactory->getClassMetaData( $entity );

        if ( ! $classMeta ) {
            return null;
        }

        $query = $entityManager->getQueryBuilder()
                               ->select( array_map( static fn( Field $field ) => $field->getDatabaseName(), $classMeta->getTable()->getFields() ) )
                               ->from( '[' . $classMeta->getTable()->getFullDatabaseName() . '] as ' . $classMeta->getTable()->getDatabaseName() );

        $query = $this->applyStateToQuery( $classMeta, $state, $entity, $query );

        if ( $arguments ) {
            $arguments->apply( $query, $classMeta->getTable() );
        }

        return $query;
    }

    private function applyStateToQuery( ClassMetadata $classMeta, array $state, string $entity, QueryBuilder $query ): QueryBuilder {
        foreach ( $state as $propertyName => $value ) {
            $field = $classMeta->getTable()->getFieldByPropertyName( $propertyName );

            if ( ! $field ) {
                continue;
            }

            if ( is_array( $value ) ) {
                foreach ( $value as $valKey => $nestedValue ) {
                    $nestedValue = $this->typeTransformer->transformToDatabaseValue( $field->getType()->getName(), $nestedValue, $entity, $field );

                    $query->{$valKey > 0 ? 'or' : 'where'}( $classMeta->getTable()->getDatabaseName() . '.' . $field->getDatabaseName() . ' = %s', $nestedValue );
                }
                continue;
            }

            $value = $this->typeTransformer->transformToDatabaseValue( $field->getType()->getName(), $value, $entity, $field );

            $query->where( $classMeta->getTable()->getDatabaseName() . '.' . $field->getDatabaseName() . ' = %s', $value );
        }

        return $query;
    }

    public function getInsertQuery( string $entity, array $state, EntityManager $entityManager ): QueryBuilder {
        $classMeta = $entityManager->getClassMetaDataFactory()->getClassMetaData( $entity );

        $values = [];
        foreach ( $state as $propertyName => $value ) {
            $field = $classMeta->getTable()->getFieldByPropertyName( $propertyName );

            if ( ! $field ) {
                continue;
            }

            $values[ $field->getDatabaseName() ] = $entityManager->getTypeTransformer()->transformToDatabaseValue(
                $field->getType()->getName(),
                $value,
                $entity,
                $field,
            );
        }

        return $entityManager->getQueryBuilder()->insert( $classMeta->getTable()->getFullDatabaseName(), $values );
    }

    public function getUpdateQuery( string $entity, array $state, EntityManager $entityManager ): QueryBuilder {
        $classMeta = $entityManager->getClassMetaDataFactory()->getClassMetaData( $entity );

        $values = [];
        foreach ( $state as $propertyName => $value ) {
            $field = $classMeta->getTable()->getFieldByPropertyName( $propertyName );

            if ( ! $field ) {
                continue;
            }

            if ( $propertyName === $classMeta->getTable()->getPrimaryKey()->getPropertyName() ) {
                continue;
            }

            $values[ $field->getDatabaseName() ] = $entityManager->getTypeTransformer()->transformToDatabaseValue(
                $field->getType()->getName(),
                $value,
                $entity,
                $field,
            );
        }

        return $entityManager->getQueryBuilder()
                             ->update( $classMeta->getTable()->getFullDatabaseName() )
                             ->set( $values )
                             ->where( $classMeta->getTable()->getPrimaryKey()->getDatabaseName() . ' = ? ', $state[ $classMeta->getTable()->getPrimaryKey()->getPropertyName() ] );
    }

    public function getDeleteQuery( string $entity, array $state, EntityManager $entityManager ): ?QueryBuilder {
        $classMeta = $this->classMetaDataFactory->getClassMetaData( $entity );

        if ( ! $classMeta ) {
            return null;
        }

        $query = $entityManager->getQueryBuilder()
                               ->delete()
                               ->from( '[' . $classMeta->getTable()->getFullDatabaseName() . '] as ' . $classMeta->getTable()->getDatabaseName() );

        return $this->applyStateToQuery( $classMeta, $state, $entity, $query );
    }

}