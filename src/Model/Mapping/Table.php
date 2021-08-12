<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Mapping;

use Swift\Kernel\Attributes\DI;

/**
 * Class Table
 * @package Swift\Model\Mapping
 */
#[DI( autowire: false )]
class Table {

    /**
     * Table constructor.
     *
     * @param string     $className
     * @param string     $databaseName
     * @param string     $databasePrefix
     * @param Field|null $primaryKey
     * @param Field[]    $fields
     * @param Index[]    $indexes
     */
    public function __construct(
        private string $className,
        private string $databaseName,
        private string $databasePrefix,
        private ?Field $primaryKey = null,
        private array  $fields = [],
        private array  $indexes = [],
    ) {
    }

    public function addField( Field $field ): static {
        $this->fields[] = $field;

        return $this;
    }

    public function addIndex( Index $index ): static {
        $this->indexes[] = $index;

        return $this;
    }

    /**
     * @return string
     */
    public function getClassName(): string {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getDatabaseName(): string {
        return $this->databaseName;
    }

    /**
     * @param Field $primaryKey
     *
     * @return Table
     */
    public function setPrimaryKey( Field $primaryKey ): static {
        if ( ! is_null( $this->primaryKey ) ) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Trying to set primary key of Entity %s to %s, but primary key is already set to %s. Multiple primary keys in a table is a no-op.',
                    $this->className,
                    $primaryKey,
                    $this->primaryKey->getDatabaseName(),
                ) );
        }

        $this->primaryKey = $primaryKey;

        return $this;
    }

    /**
     * @return Field|null
     */
    public function getPrimaryKey(): ?Field {
        return $this->primaryKey;
    }

    public function hasIndexByDatabaseName( string $name ): bool {
        return ! is_null( $this->getIndexByDatabaseName( $name ) );
    }

    public function getIndexByDatabaseName( string $name ): ?Index {
        foreach ( $this->getIndexes() as $index ) {
            if ( $index->getName() === $name ) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @return Index[]
     */
    public function getIndexes(): array {
        return $this->indexes;
    }

    public function hasFieldByDatabaseName( string $name ): bool {
        return ! is_null( $this->getFieldByDatabaseName( $name ) );
    }

    public function getFieldByDatabaseName( string $name ): ?Field {
        foreach ( $this->getFields() as $field ) {
            if ( $field->getDatabaseName() === $name ) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @return Field[]
     */
    public function getFields(): array {
        return $this->fields;
    }

    public function hasFieldByPropertyName( string $name ): bool {
        return ! is_null( $this->getFieldByPropertyName( $name ) );
    }

    public function getFieldByPropertyName( string $name ): ?Field {
        foreach ( $this->getFields() as $field ) {
            if ( $field->getPropertyName() === $name ) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Database name including prefix
     *
     * @return string
     */
    public function getFullDatabaseName(): string {
        return $this->getDatabasePrefix() . $this->databaseName;
    }

    /**
     * @return string
     */
    public function getDatabasePrefix(): string {
        return $this->databasePrefix;
    }


}