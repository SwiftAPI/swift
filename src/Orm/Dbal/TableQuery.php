<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Dbal;

use Dibi\Fluent;
use InvalidArgumentException;
use JetBrains\PhpStorm\Deprecated;
use Swift\Dbal\DatabaseEngine;
use Swift\Dbal\Enums\QueryAction;
use Swift\Dbal\Query;
use Swift\Dbal\QueryType;
use Swift\Orm\Mapping\Definition\Entity;
use Swift\Orm\Mapping\Definition\Field;
use Swift\Orm\Mapping\Definition\Index;
use Swift\Orm\Mapping\Definition\IndexType;

/**
 * Class TableQuery
 * @package Swift\Orm\Query
 */
#[Deprecated]
class TableQuery extends Query {
    
    /** @var Field[] $fields */
    private array $fields = [];
    /** @var Index[] $indexes */
    private array $indexes = [];
    /** @var QueryAction[] $fieldActions */
    private array $fieldActions = [];
    /** @var QueryAction[] $indexActions */
    private array $indexActions = [];
    /** @var \Dibi\Fluent|null */
    private ?Fluent $query = null;
    
    private DatabaseEngine $databaseEngine;
    
    /**
     * TableQuery constructor.
     *
     * @param string                               $databaseName
     * @param \Swift\Orm\Mapping\Definition\Entity $entity
     * @param QueryType                            $queryType
     * @param \Swift\Dbal\DatabaseEngine|null      $databaseEngine
     * @param bool                                 $dropTableIfExists
     */
    public function __construct(
        private string  $databaseName,
        private Entity  $entity,
        QueryType       $queryType,
        ?DatabaseEngine $databaseEngine = null,
        private bool    $dropTableIfExists = false,
    ) {
        if ( ! in_array( $queryType->name, [ QueryType::CREATE->name, QueryType::ALTER->name ], true ) ) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s expects %s (%s) or %s (%s) as Query Type, %s provided',
                    static::class,
                    QueryType::CREATE->name,
                    QueryType::class,
                    QueryType::ALTER->name,
                    QueryType::class,
                    $queryType->name,
                )
            );
        }
        
        $this->databaseEngine = $databaseEngine ?? DatabaseEngine::INNODB;
        
        parent::__construct( $queryType );
    }
    
    public function addField( Field $field, QueryAction $actionType ): static {
        $this->validateQueryAction( $actionType );
        
        $this->fields[ $field->getDatabaseName() ]       = $field;
        $this->fieldActions[ $field->getDatabaseName() ] = $actionType;
        
        return $this;
    }
    
    private function validateQueryAction( QueryAction $queryAction ): void {
        if ( ( $queryAction === QueryAction::MODIFY ) && ( $this->getQueryType() === QueryType::CREATE ) ) {
            throw new InvalidArgumentException( 'Cannot modify column upon table creation' );
        }
        if ( ( $queryAction === QueryAction::DROP ) && ( $this->getQueryType() === QueryType::CREATE ) ) {
            throw new InvalidArgumentException( 'Cannot drop column upon table creation' );
        }
    }
    
    public function addIndex( Index $index, QueryAction $actionType ): static {
        $this->validateQueryAction( $actionType );
        
        $this->indexes[ $index->getName() ]      = $index;
        $this->indexActions[ $index->getName() ] = $actionType;
        
        return $this;
    }
    
    public function getDatabaseEngine(): DatabaseEngine {
        return $this->databaseEngine;
    }
    
    /**
     * @inheritDoc
     */
    public function getSql(): array {
        $this->sort();
        
        $uniqueFieldActions  = array_unique( array_map( static fn( QueryAction $action ) => $action->name, array_values( $this->fieldActions ) ) );
        $uniqueIndexActions  = array_unique( array_map( static fn( QueryAction $action ) => $action->name, array_values( $this->indexActions ) ) );
        $uniqueActionStrings = array_map( static fn( string $action ): QueryAction => QueryAction::from( $action ), array_unique( [ ...$uniqueFieldActions, ...$uniqueIndexActions ] ) );
        
        if ( $uniqueActionStrings ) {
            $ref = $this;
            
            return array_map( static function ( QueryAction $action ) use ( $ref ): string {
                return $ref->doGetSql( $action );
            }, $uniqueActionStrings );
        }
        
        return [ $this->doGetSql() ];
    }
    
    /**
     * Make sure commands are executed in the right order; drop, modify, add
     */
    private function sort(): void {
        // Sort columns
        $addedColumns    = [];
        $modifiedColumns = [];
        $droppedColumns  = [];
        foreach ( $this->getFields() as $field ) {
            switch ( $this->fieldActions[ $field->getDatabaseName() ] ) {
                case QueryAction::ADD:
                    $addedColumns[] = $field;
                    break;
                case QueryAction::MODIFY:
                    $modifiedColumns[] = $field;
                    break;
                case QueryAction::DROP:
                    $droppedColumns[] = $field;
                    break;
            }
        }
        $this->fields = [
            ...$droppedColumns,
            ...$modifiedColumns,
            ...$addedColumns,
        ];
        
        // Indexes
        $addedIndexes    = [];
        $modifiedIndexes = [];
        $droppedIndexes  = [];
        foreach ( $this->getIndexes() as $index ) {
            switch ( $this->indexActions[ $index->getName() ] ) {
                case QueryAction::ADD:
                    $addedIndexes[] = $index;
                    break;
                case QueryAction::MODIFY:
                    $modifiedIndexes[] = $index;
                    break;
                case QueryAction::DROP:
                    $droppedIndexes[] = $index;
                    break;
            }
        }
        $this->indexes = [
            ...$droppedIndexes,
            ...$modifiedIndexes,
            ...$addedIndexes,
        ];
    }
    
    /**
     * @return Field[]
     */
    public function getFields(): array {
        return $this->fields;
    }
    
    /**
     * @return Index[]
     */
    public function getIndexes(): array {
        return $this->indexes;
    }
    
    private function doGetSql( ?QueryAction $mode = null ): string {
        $sql = match ( $this->getQueryType() ) {
            QueryType::CREATE => 'CREATE TABLE ' . ( $this->dropTableIfExists ? 'OR REPLACE ' : 'IF NOT EXISTS ' ) . $this->databaseName,
            QueryType::ALTER => ( $this->dropTableIfExists ? 'DROP TABLE IF EXISTS ' . $this->databaseName . ';' : '' ) . 'ALTER TABLE ' . $this->databaseName,
            QueryType::DELETE => 'DROP TABLE IF EXISTS ' . $this->databaseName,
            default => throw new InvalidArgumentException( 'Cannot create query for ' . $this->getQueryType()->name ),
        };
        
        // This completes the query already
        if ( $this->getQueryType() === QueryType::DELETE ) {
            return $sql;
        }
        
        // Open query
        $sql .= $this->getQueryType() === QueryType::CREATE ? '(' : '';
        
        // Parse columns/fields to query
        $fieldStatements = [];
        foreach ( $this->getFields() as $field ) {
            if ( $mode && ( $this->fieldActions[ $field->getDatabaseName() ] !== $mode ) ) {
                continue;
            }
            $statement = match ( $this->fieldActions[ $field->getDatabaseName() ] ) {
                QueryAction::ADD => $this->getQueryType() === QueryType::CREATE ? '' : 'ADD',
                QueryAction::MODIFY => 'MODIFY COLUMN',
                QueryAction::DROP => 'DROP COLUMN IF EXISTS',
                default => '',
            };
            
            $statement .= ' `' . $field->getDatabaseName() . '`';
            
            // Further additions are not supported
            if ( $this->fieldActions[ $field->getDatabaseName() ] === QueryAction::DROP ) {
                $fieldStatements[] = $statement;
                continue;
            }
            
            $statement .= ' ' . $field->getType()->getSqlDeclaration( $field, $this );
            $statement .= ' COMMENT ' . $field->getComment();
            
            if ( ! $field->isNullable() ) {
                $statement .= ' NOT NULL';
            }
            if ( $field->getIndex() === IndexType::PRIMARY ) {
                $statement .= ' AUTO_INCREMENT';
            }
            
            $fieldStatements[] = $statement;
        }
        $sql .= ' ' . implode( ',', $fieldStatements );
        
        $indexStatements = [];
        foreach ( $this->getIndexes() as $index ) {
            if ( $mode && ( $this->indexActions[ $index->getName() ] !== $mode ) ) {
                continue;
            }
            $action = match ( $this->indexActions[ $index->getName() ] ) {
                QueryAction::ADD => $this->getQueryType() === QueryType::CREATE ? ' ' : 'ADD',
                QueryAction::DROP => 'DROP',
                default => null,
            };
            
            // Ignore updates, this is a no-op for now
            if ( ! $action ) {
                continue;
            }
            
            $type = $this->indexActions[ $index->getName() ] === QueryAction::ADD ?
                match ( $index->getIndexType() ) {
                    IndexType::PRIMARY => sprintf( 'PRIMARY KEY (%s)', implode( ',', $index->getFieldNames() ) ),
                    IndexType::INDEX => sprintf( /** @lang text */ 'INDEX %s (%s)', $index->getName(), implode( ',', $index->getFieldNames() ) ),
                    IndexType::UNIQUE => sprintf( 'CONSTRAINT %s UNIQUE(%s)', $index->getName(), implode( ',', $index->getFieldNames() ) ),
                    default => null,
                } : match ( $index->getIndexType() ) {
                    IndexType::PRIMARY => 'PRIMARY KEY',
                    IndexType::INDEX => sprintf( /** @lang text */ 'INDEX IF EXISTS %s', $index->getName() ),
                    IndexType::UNIQUE => sprintf( 'CONSTRAINT IF EXISTS %s', $index->getName() ),
                    default => null,
                };
            
            if ( ! $type ) {
                continue;
            }
            
            $indexStatements[] = $action . ' ' . $type;
        }
        $sql .= ! empty( $indexStatements ) ? ', ' . implode( ',', $indexStatements ) : '';
        
        
        // Close query
        $sql .= $this->getQueryType() === QueryType::CREATE ? ')' : '';
        
        // Append engine
        $sql .= ', ENGINE=' . $this->databaseEngine->value;
        
        // Append comment
        $sql .= ', COMMENT ' . $this->entity->getTableComment();
        
        
        // End query
        $sql .= ';';
        
        return $sql;
    }
    
    public function setDropTableIfExists( bool $dropTableIfExists ): static {
        $this->dropTableIfExists = $dropTableIfExists;
        
        return $this;
    }
}