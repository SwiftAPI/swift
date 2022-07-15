<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping\Definition;


use Swift\DependencyInjection\Attributes\DI;

/**
 * Class Table
 * @package Swift\Orm\Mapping\Definition
 */
#[DI( autowire: false )]
class Entity {
    
    /**
     * Table constructor.
     *
     * @param string      $className
     * @param string      $databaseName
     * @param string      $databasePrefix
     * @param string|null $tableComment
     * @param Field|null  $primaryKey
     * @param Field[]     $fields
     * @param Index[]     $indexes
     * @param array       $connections
     */
    public function __construct(
        private string  $className,
        private string  $databaseName,
        private string  $databasePrefix,
        private ?string $tableComment,
        private ?Field  $primaryKey = null,
        private array   $fields = [],
        private array   $indexes = [],
        private array   $connections = [],
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
    
    public function addConnection( \Swift\Orm\Mapping\Definition\Relation\EntitiesConnection $connection ): static {
        $this->connections[] = $connection;
        
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
     * @return \Swift\Orm\Mapping\Definition\Entity
     */
    public function setPrimaryKey( Field $primaryKey ): static {
        if ( ! is_null( $this->primaryKey ) ) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Trying to set primary key of Entity %s to %s, but primary key is already set to %s. Multiple primary keys in a table is a no-op.',
                    $this->className,
                    $primaryKey->getDatabaseName(),
                    $this->primaryKey->getDatabaseName(),
                )
            );
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
    
    /**
     * @return \Swift\Orm\Mapping\Definition\Relation\EntitiesConnection[]
     */
    public function getConnections(): array {
        return $this->connections;
    }
    
    /**
     * @return string
     */
    public function getTableComment(): string {
        return sprintf(
            '"ENTITY=%s%s"',
            str_replace( '\\', '/', $this->getClassName() ),
            $this->tableComment ? ', ' . $this->tableComment : '',
        );
    }
    
}