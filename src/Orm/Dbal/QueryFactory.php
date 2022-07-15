<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Dbal;

use JetBrains\PhpStorm\Deprecated;
use Swift\Dbal\Arguments\Arguments;
use Swift\Dbal\Driver\DatabaseDriver;
use Swift\Dbal\QueryBuilder;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Orm\Dbal\Helper\QueryHelper;
use Swift\Orm\Factory;
use Swift\Orm\Mapping\ClassMetaData;
use Swift\Orm\Mapping\ClassMetaDataFactory;
use Swift\Orm\Types\TypeTransformer;

#[Autowire]
class QueryFactory {
    
    public function __construct(
        private readonly ClassMetaDataFactory $classMetaDataFactory,
        private readonly TypeTransformer      $typeTransformer,
        private readonly DatabaseDriver       $databaseDriver,
        private readonly Factory              $ormFactory,
    ) {
    }
    
    /**
     * @param string                               $entity
     * @param array                                $state
     * @param \Swift\Dbal\Arguments\Arguments|null $arguments
     *
     * @return QueryBuilder|null
     */
    public function getSelectQuery( string $entity, array $state, ?Arguments $arguments ): ?\Cycle\ORM\Select {
        $classMeta = $this->classMetaDataFactory->getClassMetaData( $entity );
        
        if ( ! $classMeta ) {
            return null;
        }
    
        $repository = $this->ormFactory->getOrm()->getRepository( $entity );
        
        $query = $repository->select();
        
        $query = $this->applyStateToQuery( $classMeta, $state, $entity, $query );
        
        $arguments?->apply( $query, $classMeta->getEntity() );
        
        return $query;
    }
    
    public function applyStateToQuery( ClassMetadata $classMeta, array $state, string $entity, \Cycle\ORM\Select $query ): \Cycle\ORM\Select {
        foreach ( $state as $propertyName => $value ) {
            $field = $classMeta->getEntity()->getFieldByPropertyName( $propertyName );
            
            if ( ! $field ) {
                continue;
            }
            
            if ( is_array( $value ) ) {
                foreach ( $value as $valKey => $nestedValue ) {
                    //$nestedValue = $this->typeTransformer->transformToDatabaseValue( $field->getType()->getName(), $nestedValue, $entity, $field );
                    
                    $query->{$valKey > 0 ? 'or' : 'where'}( $classMeta->getEntity()->getDatabaseName() . '.' . $field->getDatabaseName() . ' = ?', $nestedValue );
                }
                continue;
            }
            
            //$value = $this->typeTransformer->transformToDatabaseValue( $field->getType()->getName(), $value, $entity, $field );
            
            $query->where( $classMeta->getEntity()->getDatabaseName() . '.' . $field->getDatabaseName(), ' = ', $value );
        }
        
        return $query;
    }
    
    #[Deprecated]
    public function getInsertQuery( string $entity, array $state ): QueryBuilder {
        $classMeta = $this->classMetaDataFactory->getClassMetaData( $entity );
        
        $values = [];
        foreach ( $state as $propertyName => $value ) {
            $field = $classMeta->getEntity()->getFieldByPropertyName( $propertyName );
            
            if ( ! $field ) {
                continue;
            }
            
            $values[ $field->getDatabaseName() ] = $this->typeTransformer->transformToDatabaseValue(
                $field->getType()->getName(),
                $value,
                $entity,
                $field,
            );
        }
        
        return $this->databaseDriver->getQueryBuilder()->insert( $classMeta->getEntity()->getFullDatabaseName(), $values );
    }
    
    #[Deprecated]
    public function getUpdateQuery( string $entity, array $state ): QueryBuilder {
        $classMeta = $this->classMetaDataFactory->getClassMetaData( $entity );
        
        $values = [];
        foreach ( $state as $propertyName => $value ) {
            $field = $classMeta->getEntity()->getFieldByPropertyName( $propertyName );
            
            if ( ! $field ) {
                continue;
            }
            
            if ( $propertyName === $classMeta->getEntity()->getPrimaryKey()->getPropertyName() ) {
                continue;
            }
            
            $values[ $field->getDatabaseName() ] = $this->typeTransformer->transformToDatabaseValue(
                $field->getType()->getName(),
                $value,
                $entity,
                $field,
            );
        }
        
        return $this->databaseDriver->getQueryBuilder()
            ->update( $classMeta->getEntity()->getFullDatabaseName() )
            ->set( $values )
            ->where( $classMeta->getEntity()->getPrimaryKey()->getDatabaseName() . ' = ? ', $state[ $classMeta->getEntity()->getPrimaryKey()->getPropertyName() ] );
    }
    
    #[Deprecated]
    public function getDeleteQuery( string $entity, array $state ): ?QueryBuilder {
        $classMeta = $this->classMetaDataFactory->getClassMetaData( $entity );
        
        if ( ! $classMeta ) {
            return null;
        }
        
        $query = $this->databaseDriver->getQueryBuilder()
            ->delete()
            ->from( '[' . $classMeta->getEntity()->getFullDatabaseName() . '] as ' . $classMeta->getEntity()->getDatabaseName() );
        
        return $this->applyStateToQuery( $classMeta, $state, $entity, $query );
    }
    
}