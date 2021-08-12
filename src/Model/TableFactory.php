<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model;

use Swift\Configuration\ConfigurationInterface;
use Swift\Kernel\Attributes\Autowire;
use Swift\Model\Driver\DatabaseDriver;
use Swift\Model\Driver\TableColumnReflection;
use Swift\Model\Driver\TableIndexReflection;
use Swift\Model\Driver\TableMetaDataFactory;
use Swift\Model\Mapping\ClassMetaDataFactory;
use Swift\Model\Mapping\QueryActionType;
use Swift\Model\Query\QueryType;
use Swift\Model\Query\TableQuery;
use Swift\Model\Types\TypeTransformer;

/**
 * Class TableFactory
 * @package Swift\Model
 */
#[Autowire]
class TableFactory {

    /**
     * TableFactory constructor.
     */
    public function __construct(
        private ClassMetaDataFactory   $classMetaDataFactory,
        private TableMetaDataFactory   $tableMetaDataFactory,
        private ConfigurationInterface $configuration,
        private TypeTransformer        $typeTransformer,
        private DatabaseDriver         $databaseDriver,
    ) {
    }

    public function createOrUpdateTable( string $entity, bool $removeNonExistingColumns, bool $dropTableIfExists ): TableCreateOrUpdateResult {
        $classMeta       = $this->classMetaDataFactory->getClassMetaData( $entity );
        $tableReflection = $this->tableMetaDataFactory->getTableReflection( $classMeta->getTable()->getFullDatabaseName() );
        $queryType       = new QueryType( is_null( $tableReflection ) || $dropTableIfExists ? QueryType::CREATE : QueryType::ALTER );

        $query = new TableQuery(
            $classMeta->getTable()->getFullDatabaseName(),
            $queryType,
            new DatabaseEngine( $this->configuration->get( 'connection.engine', 'database' ) ),
            $dropTableIfExists,
        );

        // Add fields to query
        foreach ( $classMeta->getTable()->getFields() as $field ) {
            // Determine to-perform action
            $queryAction = $tableReflection?->hasColumn( $field->getDatabaseName() ) ?
                new QueryActionType( QueryActionType::MODIFY ) : new QueryActionType( QueryActionType::ADD );

            $query->addField( $field, $queryAction );
        }

        // Add indexes to query
        foreach ( $classMeta->getTable()->getIndexes() as $index ) {
            // Determine to-perform action
            $queryAction = $tableReflection?->hasIndex( $index->getName() ) ?
                new QueryActionType( QueryActionType::MODIFY ) : new QueryActionType( QueryActionType::ADD );

            $query->addIndex( $index, $queryAction );
        }

        // Non-existing columns
        $nonExistingColumns = [];
        foreach ( $tableReflection->getColumns() as $column ) {
            if ( $classMeta->getTable()->hasFieldByDatabaseName( $column->getName() ) ) {
                continue;
            }

            $field                = TableColumnReflection::toField( $column, $entity, $this->typeTransformer->getType( $column->getNativeType() ) );
            $nonExistingColumns[] = $field;

            if ( $removeNonExistingColumns ) {
                $query->addField( $field, new QueryActionType( QueryActionType::DROP ) );
            }
        }

        // Non-existing indexes
        $nonExistingIndexes = [];
        foreach ( $tableReflection->getIndexes() as $index ) {
            if ( $classMeta->getTable()->hasIndexByDatabaseName( $index->getName() ) ) {
                continue;
            }

            $index                = TableIndexReflection::toIndex( $index );
            $nonExistingIndexes[] = $index;

            if ( $removeNonExistingColumns ) {
                $query->addIndex( $index, new QueryActionType( QueryActionType::DROP ) );
            }
        }

        $this->databaseDriver->nativeQuery( $query->getSql() );

        return new TableCreateOrUpdateResult(
            $queryType,
            $nonExistingColumns,
            $nonExistingIndexes,
        );
    }

}