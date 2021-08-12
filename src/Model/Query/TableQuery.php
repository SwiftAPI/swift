<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Query;

use Dibi\Fluent;
use InvalidArgumentException;
use Swift\Model\DatabaseEngine;
use Swift\Model\Driver\DatabaseDriver;
use Swift\Model\Mapping\Field;
use Swift\Model\Mapping\Index;
use Swift\Model\Mapping\IndexType;
use Swift\Model\Mapping\QueryActionType;

/**
 * Class TableQuery
 * @package Swift\Model\Query
 */
class TableQuery extends Query {

    /** @var Field[] $fields */
    private array $fields = [];
    /** @var Index[] $indexes */
    private array $indexes = [];
    /** @var QueryActionType[] $fieldActions */
    private array $fieldActions = [];
    /** @var QueryActionType[] $indexActions */
    private array $indexActions = [];
    /** @var \Dibi\Fluent|null */
    private ?Fluent $query = null;

    private DatabaseEngine $databaseEngine;

    /**
     * TableQuery constructor.
     *
     * @param string                           $databaseName
     * @param QueryType                        $queryType
     * @param \Swift\Model\DatabaseEngine|null $databaseEngine
     * @param bool                             $dropTableIfExists
     */
    public function __construct(
        private string  $databaseName,
        QueryType       $queryType,
        ?DatabaseEngine $databaseEngine = null,
        private bool    $dropTableIfExists = false,
    ) {
        if ( ! in_array( $queryType->getValue(), [ QueryType::CREATE, QueryType::ALTER ], true ) ) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s expects %s (%s) or %s (%s) as Query Type, %s provided',
                    static::class,
                    QueryType::CREATE,
                    QueryType::class,
                    QueryType::ALTER,
                    QueryType::class,
                    $queryType->getValue(),
                ) );
        }

        $this->databaseEngine = $databaseEngine ?? new DatabaseEngine( DatabaseEngine::INNODB );

        parent::__construct( $queryType );
    }

    public function addField( Field $field, QueryActionType $actionType ): static {
        $this->validateQueryActionType( $actionType );

        $this->fields[ $field->getDatabaseName() ]       = $field;
        $this->fieldActions[ $field->getDatabaseName() ] = $actionType;

        return $this;
    }

    private function validateQueryActionType( QueryActionType $queryActionType ): void {
        if ( ( $queryActionType->getValue() === QueryActionType::MODIFY ) && ( $this->getQueryType()->getValue() === QueryType::CREATE ) ) {
            throw new InvalidArgumentException( 'Cannot modify column upon table creation' );
        }
        if ( ( $queryActionType->getValue() === QueryActionType::DROP ) && ( $this->getQueryType()->getValue() === QueryType::CREATE ) ) {
            throw new InvalidArgumentException( 'Cannot drop column upon table creation' );
        }
    }

    public function addIndex( Index $index, QueryActionType $actionType ): static {
        $this->validateQueryActionType( $actionType );

        $this->indexes[ $index->getName() ]      = $index;
        $this->indexActions[ $index->getName() ] = $actionType;

        return $this;
    }

    public function getDatabaseEngine(): DatabaseEngine {
        return $this->databaseEngine;
    }

    /**
     * Make sure commands are executed in the right order; drop, modify, add
     */
    private function sort(): void {
        // Sort columns
        $addedColumns = [];
        $modifiedColumns = [];
        $droppedColumns = [];
        foreach ($this->getFields() as $field) {
            switch($this->fieldActions[ $field->getDatabaseName() ]->getValue()) {
                case QueryActionType::ADD:
                    $addedColumns[] = $field;
                    break;
                case QueryActionType::MODIFY:
                    $modifiedColumns[] = $field;
                    break;
                case QueryActionType::DROP:
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
        $addedIndexes = [];
        $modifiedIndexes = [];
        $droppedIndexes = [];
        foreach ($this->getIndexes() as $index) {
            switch($this->indexActions[ $index->getName() ]->getValue()) {
                case QueryActionType::ADD:
                    $addedIndexes[] = $index;
                    break;
                case QueryActionType::MODIFY:
                    $modifiedIndexes[] = $index;
                    break;
                case QueryActionType::DROP:
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
     * @inheritDoc
     */
    public function getSql(): string {
        $this->sort();
        $sql = match ( $this->getQueryType()->getValue() ) {
            QueryType::CREATE => 'CREATE TABLE ' . ($this->dropTableIfExists ? 'OR REPLACE ' : 'IF NOT EXISTS ') . $this->databaseName,
            QueryType::ALTER => ($this->dropTableIfExists ? 'DROP TABLE IF EXISTS ' . $this->databaseName . ';' : '') . 'ALTER TABLE ' . $this->databaseName,
            QueryType::DELETE => 'DROP TABLE IF EXISTS ' . $this->databaseName,
            default => throw new InvalidArgumentException( 'Cannot create query for ' . $this->getQueryType()->getValue() ),
        };

        // This completes the query already
        if ( $this->getQueryType()->getValue() === QueryType::DELETE ) {
            return $sql;
        }

        // Open query
        $sql .= $this->getQueryType()->getValue() === QueryType::CREATE ? '(' : '';

        // Parse columns/fields to query
        $fieldStatements = [];
        foreach ( $this->getFields() as $field ) {
            $statement = match ( $this->fieldActions[ $field->getDatabaseName() ]->getValue() ) {
                QueryActionType::ADD => $this->getQueryType()->getValue() === QueryType::CREATE ? '' : 'ADD',
                QueryActionType::MODIFY => 'MODIFY COLUMN',
                default => '',
            };

            $statement .= ' ' . $field->getDatabaseName();
            $statement .= ' ' . $field->getType()->getSqlDeclaration( $field, $this );
            $statement .= ' COMMENT ' . $field->getComment();

            if ( ! $field->isNullable() ) {
                $statement .= ' NOT NULL';
            }
            if ( $field->getIndex()?->getValue() === IndexType::PRIMARY ) {
                $statement .= ' AUTO_INCREMENT';
            }

            $fieldStatements[] = $statement;
        }
        $sql .= ' ' . implode( ',', $fieldStatements );

        $indexStatements = [];
        foreach ( $this->getIndexes() as $index ) {
            $action = match ( $this->indexActions[ $index->getName() ]->getValue() ) {
                QueryActionType::ADD => 'ADD',
                QueryActionType::DROP => 'DROP',
                default => null,
            };

            // Ignore updates, this is a no-op for now
            if ( ! $action ) {
                continue;
            }

            $type = $action === QueryActionType::ADD ? match ( $index->getIndexType()->getValue() ) {
                IndexType::PRIMARY => sprintf( 'PRIMARY KEY (%s)', implode(',', $index->getFieldNames()) ),
                IndexType::INDEX => sprintf( /** @lang text */ 'INDEX %s (%s)', $index->getName(), implode( ',', $index->getFieldNames() ) ),
                IndexType::UNIQUE => sprintf( 'CONSTRAINT %s UNIQUE(%s)', $index->getName(), implode( ',', $index->getFieldNames() ) ),
                default => null,
            } : match ( $index->getIndexType()->getValue() ) {
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
        $sql .= $this->getQueryType()->getValue() === QueryType::CREATE ? ')' : '';

        // Append engine
        $sql .= ', ENGINE=InnoDB';

        return $sql;
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

    public function setDropTableIfExists( bool $dropTableIfExists ): static {
        $this->dropTableIfExists = $dropTableIfExists;

        return $this;
    }
}