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
use Swift\DependencyInjection\Attributes\DI;

#[DI( autowire: false )]
#[Deprecated]
class TableReflection {

    /**
     * @param string                  $name
     * @param TableColumnReflection[] $columns
     * @param TableIndexReflection[]  $indexes
     * @param array                   $foreignKeys
     */
    public function __construct(
        private string $name,
        private array  $columns = [],
        private array  $indexes = [],
        private array  $foreignKeys = [],
    ) {
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    public function hasColumn( string $name ): bool {
        return ! is_null( $this->getColumn( $name ) );
    }

    public function getColumn( string $name ): ?TableColumnReflection {
        foreach ( $this->getColumns() as $column ) {
            if ( $name === $column->getName() ) {
                return $column;
            }
        }

        return null;
    }

    /**
     * @return \Swift\Orm\Driver\TableColumnReflection[]
     */
    public function getColumns(): array {
        return $this->columns;
    }

    public function hasIndex( string $name ): bool {
        return ! is_null( $this->getIndex( $name ) );
    }

    public function getIndex( string $name ): ?TableIndexReflection {
        foreach ( $this->getIndexes() as $index ) {
            if ( $name === $index->getName() ) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @return \Swift\Orm\Driver\TableIndexReflection[]
     */
    public function getIndexes(): array {
        return $this->indexes;
    }

    /**
     * @return array
     */
    public function getForeignKeys(): array {
        return $this->foreignKeys;
    }


}