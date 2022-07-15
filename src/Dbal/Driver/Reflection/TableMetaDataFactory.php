<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Dbal\Driver\Reflection;

use JetBrains\PhpStorm\Deprecated;
use Swift\Dbal\Driver\DatabaseDriver;
use Swift\DependencyInjection\Attributes\Autowire;

/**
 * @package Swift\Orm\Driver\TableMetaDataFactory
 */
#[Autowire]
#[Deprecated]
class TableMetaDataFactory {

    /** @var string[] $presentColumns */
    private array $presentColumns;

    /**
     * @param DatabaseDriver $databaseDriver
     */
    public function __construct(
        private DatabaseDriver $databaseDriver,
    ) {
        $this->presentColumns = array_map(
            static fn( array $table ): string => $table[ 'name' ],
            $this->databaseDriver->getDriver()->getReflector()->getTables()
        );
    }

    /**
     * @param string $table
     *
     * @return \Swift\Dbal\Driver\Reflection\TableReflection|null
     */
    public function getTableReflection( string $table ): ?TableReflection {
        if ( ! in_array( $table, $this->presentColumns, true ) ) {
            return null;
        }

        return new TableReflection(
            $table,
            $this->getTableColumns( $table ) ?? [],
            $this->getTableIndexes( $table ) ?? [],
            $this->getTableForeignKeys( $table ) ?? [],
        );
    }

    /**
     * @param string $table
     *
     * @return TableColumnReflection[]|null
     */
    public function getTableColumns( string $table ): ?array {
        $columns = $this->databaseDriver->getDriver()->getReflector()->getColumns( $table );

        return ! empty( $columns ) ? array_map( static fn( array $column ): TableColumnReflection => new TableColumnReflection( ...$column ), $columns ) : null;
    }

    /**
     * @param string $table
     *
     * @return TableIndexReflection[]|null
     */
    public function getTableIndexes( string $table ): ?array {
        $indexes = $this->databaseDriver->getDriver()->getReflector()->getIndexes( $table );

        return ! empty( $indexes ) ? array_map( static fn( array $index ): TableIndexReflection => new TableIndexReflection( ...$index ), $indexes ) : null;
    }

    public function getTableForeignKeys( string $table ): ?array {
        $foreignKeys = $this->databaseDriver->getDriver()->getReflector()->getForeignKeys( $table );

        return $foreignKeys;
    }

}