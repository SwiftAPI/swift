<?php declare( strict_types=1 );

namespace Swift\Model;

use Exception;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use ReflectionClass;
use RuntimeException;
use stdClass;
use Swift\Database\DatabaseDriver;
use Swift\Events\EventDispatcher;
use Swift\Kernel\ContainerService\ContainerService;
use Swift\Kernel\DiTags;
use Swift\Kernel\TypeSystem\Enum;
use Swift\Model\Attributes\DB;
use Swift\Model\Attributes\DBField;
use Swift\Model\Entity\EntityManager;
use Swift\Model\Entity\Helper\Query;
use Swift\Model\Events\EntityOnFieldSerializeEvent;
use Swift\Model\Events\EntityOnFieldUnSerializeEvent;
use Swift\Model\Exceptions\DatabaseException;
use Swift\Model\Exceptions\InvalidConfigurationException;
use Swift\Model\Exceptions\NoResultException;
use Swift\Model\Types\FieldTypes;
use Swift\Model\Entity\Arguments;
use Swift\Kernel\Attributes\DI;

/**
 * Class Entity
 * @package Swift\Model\Entity
 */
#[DI( tags: [ DiTags::ENTITY ] )]
abstract class Entity {

    /**
     * Entity constructor.
     *
     * @param DatabaseDriver $database
     * @param EventDispatcher $dispatcher
     * @param Query $helperQuery
     * @param ContainerService|null $container
     * @param ReflectionClass|null $reflectionClass Reflection of current class
     * @param string|null $primaryKey the primary key in the table
     * @param array $propertyMap map of properties and their belonging name in the table
     * @param array $propertyActions actions to related to properties
     * @param array $propertyProps properties and their props/settings
     * @param string|null $tableName table name without prefix
     * @param string|null $tableNamePrefixed prefixed version of the table name
     * @param array $indexes columns with indexes, referred by db_key
     */
    public function __construct(
        protected DatabaseDriver $database,
        protected EventDispatcher $dispatcher,
        protected Query $helperQuery,
        protected ?ContainerService $container,
        protected ?ReflectionClass $reflectionClass = null,
        protected ?string $primaryKey = null,
        protected array $propertyMap = array(),
        protected array $propertyActions = array(),
        protected array $propertyProps = array(),
        protected ?string $tableName = null,
        protected ?string $tableNamePrefixed = null,
        protected array $indexes = array(),
    ) {
        $this->reflectionClass = new ReflectionClass( static::class );

        $this->setTable();
        $this->mapProperties();
    }

    /**
     * Fetch a single row by the given state
     *
     * @param array|stdClass $state
     * @param bool $exceptionOnNotFound
     *
     * @return stdClass|null
     */
    public function findOne( array|stdClass $state, bool $exceptionOnNotFound = false ): ?stdClass {
        $result = $this->find( $state, new Arguments( limit: 1 ), $exceptionOnNotFound );

        return $result[0] ?? null;
    }

    /**
     * Fetch all rows matching the given state and arguments
     *
     * @param array|stdClass $state
     * @param Arguments|null $arguments
     * @param bool $exceptionOnNotFound
     *
     * @return array
     */
    public function find( array|stdClass $state, Arguments|null $arguments = null, bool $exceptionOnNotFound = false ): array {
        $state = (array) $state;
        $query = $this->database->select( '[*]' )->from( '[' . $this->tableNamePrefixed . ']' );

        foreach ( $state as $propertyName => $value ) {
            if (is_array($value)) {
                foreach ($value as $valKey => $valueItem) {
                    $valueItem = $this->onBeforeSave( $valueItem, $propertyName );

                    $func = $valKey > 0 ? 'or' : 'where';
                    $query->{$func}( $this->getPropertyDBName( $propertyName ) . ' = %s', $valueItem );
                }
                continue;
            }
            $value = $this->onBeforeSave( $value, $propertyName );

            $query->where( $this->getPropertyDBName( $propertyName ) . ' = %s', $value );
        }

        if ( $arguments ) {
            $arguments->apply( $query, $this->propertyMap, $this->primaryKey );
        }

        $results = $query->fetchAll();

        if ( $exceptionOnNotFound && is_null( $results ) ) {
            throw new NoResultException( sprintf( 'No result found for search in %s', __CLASS__ ) );
        }

        $items = array();
        foreach ( $results as $result ) {
            $item = new stdClass();
            foreach ( $result->toArray() as $key => $value ) {
                $property = array_search( $key, $this->propertyMap, true );
                if ( $property && property_exists( $this, $property ) ) {
                    $item->{$property} = $this->onAfterLoad( $value, $property );
                }
            }
            $items[] = $item;
        }

        return $items;
    }

    /**
     * Method to save/update based on the current state
     *
     * @param array|stdClass $state
     *
     * @return void
     */
    public function save( array|stdClass $state ): stdClass {
        $state = (array) $state;

        // Check if record is new
        $isNew = empty( $state[ $this->primaryKey ] );

        try {
            if ( $isNew ) { // Insert
                $this->database->query( 'INSERT INTO ' . $this->tableNamePrefixed, $this->getValuesForDatabase( $state ) );

                return $this->findOne( array( $this->primaryKey => $this->database->getInsertId() ) );
            }

            // Update
            $this->database->query(
                'UPDATE ' . $this->tableNamePrefixed . ' SET',
                $this->getValuesForDatabase( $state ),
                'WHERE ' . $this->primaryKey . ' = ?', $state[ $this->primaryKey ]
            );
            return $this->findOne( array( $this->primaryKey => $state[ $this->primaryKey ] ) );
        } catch ( \Dibi\Exception $exception ) {
            throw new DatabaseException( $exception->getMessage(), $exception->getCode(), $exception );
        }
    }

    /**
     * Method to delete a row from the database
     *
     * @param mixed $key
     *
     * @return int  number of affected rows by deletion
     */
    public function delete( mixed $key ): int {
        try {
            $this->database->query(
                'DELETE FROM ' . $this->tableNamePrefixed . ' 
					WHERE ' . $this->primaryKey . ' = ' . $key
            );

            return $this->database->getAffectedRows();
        } catch ( \Dibi\Exception $exception ) {
            throw new DatabaseException( $exception->getMessage(), $exception->getCode(), $exception );
        }
    }

    /**
     * Method to get all properties as array
     *
     * @param bool $serialized
     *
     * @return array
     */
    public function getPropertiesAsArray( bool $serialized = false ): array {
        $values = array();
        foreach ( $this->propertyMap as $propertyName => $dbBinding ) {
            $value                   = $serialized && $this->{$propertyName} ? $this->onBeforeSave( $this->{$propertyName}, $propertyName ) : $this->{$propertyName};
            $values[ $propertyName ] = $value;
        }

        return $values;
    }

    /**
     * @param bool $serialized
     *
     * @return stdClass
     */
    public function getPropertiesAsObject( bool $serialized = false ): stdClass {
        $values = new stdClass();
        foreach ( $this->propertyMap as $propertyName => $dbBinding ) {
            $value                   = $serialized && $this->{$propertyName} ? $this->onBeforeSave( $this->{$propertyName}, $propertyName ) : $this->{$propertyName};
            $values->{$propertyName} = $value;
        }

        return $values;
    }

    /**
     * Method to get all properties as array with db bindings as key
     *
     * @param array $state
     *
     * @return array
     */
    protected function getValuesForDatabase( array $state ): array {
        $values = array();
        foreach ( $this->propertyMap as $propertyName => $dbBinding ) {
            if ( array_key_exists( key: $propertyName, array: $state ) ) {
                $values[ $dbBinding ] = $this->onBeforeSave( $state[ $propertyName ], $propertyName );
            }
        }

        return $values;
    }

    /**
     * On before save event
     *
     * @param $value
     * @param $propertyName
     *
     * @return mixed
     */
    protected function onBeforeSave( $value, $propertyName ): mixed {
        // If enum; test validity
        if ( $this->propertyProps[ $propertyName ]->enum ) {
            $enumClass = $this->propertyProps[ $propertyName ]->enum;
            new $enumClass($value);
        }
        if ( array_key_exists( $propertyName, $this->propertyActions['serialize'] ) ) {
            foreach ( $this->propertyActions['serialize'][ $propertyName ] as $action ) {
                /** @var EntityOnFieldSerializeEvent $response */
                $response = $this->dispatcher->dispatch( new EntityOnFieldSerializeEvent( entity: $this, action: $action, name: $propertyName, value: $value ) );
                $value    = $response->value;
            }
        }

        return $value;
    }

    /**
     * On after load event
     *
     * @param $value
     * @param $propertyName
     *
     * @return mixed
     */
    protected function onAfterLoad( $value, $propertyName ): mixed {
        if ( array_key_exists( $propertyName, $this->propertyActions['serialize'] ) ) {
            foreach ( $this->propertyActions['serialize'][ $propertyName ] as $action ) {
                /** @var EntityOnFieldUnSerializeEvent $response */
                $response = $this->dispatcher->dispatch( new EntityOnFieldUnSerializeEvent( entity: $this, action: $action, name: $propertyName, value: $value ) );
                $value    = $response->value;
            }
        }

        return $value;
    }

    /**
     * Method to get property where clause
     *
     * @param string $propertyName name of the property to get
     *
     * @return array
     */
    #[ArrayShape( [ 'prepare' => "string", 'value' => "mixed" ] )]
    public function getPropertyWhereClause( string $propertyName ): array {
        if ( ! $this->hasField( $propertyName ) ) {
            throw new InvalidArgumentException( 'Property ' . $propertyName . ' not found', 500 );
        }

        return array(
            'prepare' => $this->propertyMap[ $propertyName ] . ' = %s ',
            'value'   => $this->{$propertyName}
        );
    }

    /**
     * Method to validate if a fieldName(property) is available
     *
     * @param string $fieldName
     *
     * @return bool
     */
    #[Pure] public function hasField( string $fieldName ): bool {
        return array_key_exists( $fieldName, $this->propertyMap );
    }

    /**
     * Method to get property from object
     *
     * @param string $property
     *
     * @return mixed
     * @throws Exception
     */
    public function get( string $property ): mixed {
        // Only this class itself or EntityManager are allowed access
        $calling_class = debug_backtrace( 1, 1 )[0]['class'];
        if ( ! is_a( $calling_class, EntityManager::class, true ) &&
             ! is_a( $calling_class, __CLASS__, true ) ) {
            throw new RuntimeException( 'Access to method not allowed', 500 );
        }

        if ( ! property_exists( $this, $property ) ) {
            throw new InvalidArgumentException( 'Property not found', 500 );
        }

        return $this->{$property};
    }

    /**
     * Method to get property's db name
     *
     * @param string $property
     *
     * @return string
     */
    public function getPropertyDBName( string $property ): string {
        // Only this class itself or EntityManager are allowed access
        $calling_class = debug_backtrace( 1, 1 )[0]['class'];
        if ( ! is_a( $calling_class, EntityManager::class, true ) &&
             ! is_a( $calling_class, __CLASS__, true ) ) {
            throw new RuntimeException( 'Access to method not allowed', 500 );
        }

        if ( ! $this->hasField( $property ) ) {
            throw new InvalidArgumentException( 'Property ' . $property . ' does not exist for ' . get_class( $this ), 500 );
        }

        return $this->propertyMap[ $property ];
    }

    /**
     * Populate table name from DB attribute
     *
     * @return void
     */
    protected function setTable(): void {
        $annotations = $this->reflectionClass->getAttributes( name: DB::class );

        if ( empty( $annotations ) ) {
            throw new InvalidConfigurationException( sprintf( 'Entity %s missing DB attribute, this is an invalid use case. Please add %s attribute to class', static::class, DB::class ) );
        }

        $annotation = $annotations[0]->getArguments();

        if ( empty( $annotation['table'] ) ) {
            throw new InvalidConfigurationException( sprintf( 'Entity %s is missing valid %s attribute configuration', static::class, DB::class ) );
        }

        $this->tableName         = $annotation['table'];
        $this->tableNamePrefixed = $this->database->getPrefix() . $this->tableName;
    }

    /**
     * Method to get table name
     *
     * @param bool $prefixed
     *
     * @return string
     */
    public function getTableName( bool $prefixed = true ): string {
        return $prefixed ? $this->tableNamePrefixed : $this->tableName;
    }

    /**
     * Method to map object properties to table columns
     *
     * @return void
     */
    protected function mapProperties(): void {
        $this->propertyActions['serialize'] = array();
        $properties                         = $this->reflectionClass->getProperties();
        foreach ( $properties as $property ) {
            $annotation = ! empty( $property->getAttributes( name: DBField::class ) ) ? $property->getAttributes( name: DBField::class )[0]->getArguments() : null;

            if ( empty( $annotation ) ) {
                continue;
            }

            $annotation = (object) $annotation;

            if ( isset( $property->name, $annotation->name ) ) {
                $this->propertyMap[ $property->name ] = $annotation->name;

                $propertyProps                          = new stdClass();
                $propertyProps->name                    = $annotation->name;
                $propertyProps->type                    = $annotation->type ?? FieldTypes::TEXT;
                $propertyProps->length                  = $annotation->length ?? 0;
                $propertyProps->primary                 = $annotation->primary ?? false;
                $propertyProps->serialize               = $annotation->serialize ?? array();
                $propertyProps->empty                   = $annotation->empty ?? false;
                $propertyProps->unique                  = $annotation->unique ?? false;
                $propertyProps->index                   = $annotation->index ?? false;
                $propertyProps->enum                    = $annotation->enum ?? null;
                $this->propertyProps[ $property->name ] = $propertyProps;

                if ($propertyProps->index) {
                    $this->indexes[] = $propertyProps->name;
                }
            }

            if ( isset( $property->name, $annotation->serialize ) && $annotation && ! empty( $annotation->serialize ) ) {
                // Set serialize actions
                $this->propertyActions['serialize'][ $property->name ] = $annotation->serialize;
            }

            // Check for primary key
            if ( isset( $property->name, $annotation->primary ) && $annotation ) {
                if ( $annotation->primary && isset( $this->primaryKey ) ) {
                    throw new InvalidConfigurationException( 'Multiple primary keys found' );
                }

                if ( $annotation->primary ) {
                    $this->primaryKey = $property->name;
                }
            }
        }
    }

    /**
     * Method to get property name by db name
     *
     * @param string $dbName
     *
     * @return string|null
     */
    #[Pure] private function getPropertyNameByDbName( string $dbName ): ?string {
        $property = array_search( $dbName, $this->propertyMap, true );

        return ( $property && property_exists( $this, $property ) ) ? $property : null;
    }

    /**
     * Method to reset properties
     */
    public function reset(): void {
        foreach ( $this->propertyMap as $propertyName => $dbName ) {
            $this->{$propertyName} = null;
        }
    }

    /**
     * Method to create entity table
     *
     * @param bool $dropTableIfExists
     *
     * @return bool
     * @throws \Dibi\Exception
     */
    public function createTable( bool $dropTableIfExists = false ): bool {
        if ( $dropTableIfExists ) {
            $this->database->query( 'DROP TABLE if EXISTS ' . $this->tableNamePrefixed );
        }

        $query = 'CREATE TABLE ' . $this->tableNamePrefixed . ' (';
        $count = 1;
        foreach ( $this->propertyProps as $propertyProp ) {
            $query .= $this->helperQuery->getCreateQueryForProperty( $propertyProp );
            $query .= $count < count( $this->propertyProps ) ? ',' : '';
            $count ++;
        }
        if ( $this->primaryKey ) {
            $query .= ',PRIMARY KEY (' . $this->propertyMap[ $this->primaryKey ] . ')';
        }
        $query .= ' );';

        $this->database->query( $query );

        if (!empty($this->indexes)) {
            $this->updateTable(false, false);
        }

        return true;
    }

    /**
     * Method to update a table by entity properties
     *
     * @param bool $removeNonExistingColumns
     * @param bool $dropTableIfExists
     *
     * @return array    Array of non properties which only exist in the database and are not present as properties.
     *                  If $removeNonExistingColumns = true, then will have been dropped from the table.
     *                  Mind this is dangerous in a production environment!
     * @throws \Dibi\Exception
     */
    public function updateTable( bool $removeNonExistingColumns, bool $dropTableIfExists ): array {
        $currentColumns     = $this->getTableColumns();
        $nonExistingColumns = array();
        $indexesToAdd = array();
        $indexesToRemove = array();

        // Table does not exist yet
        if ( is_null( $currentColumns ) ) {
            $this->createTable( false );
            throw new DatabaseException( 'Table ' . $this->tableNamePrefixed . ' did not exist yet. It has been created.' );
        }

        // Table dropped and newly created
        if ( $dropTableIfExists ) {
            $this->createTable( false );
            throw new DatabaseException( 'Table ' . $this->tableNamePrefixed . ' has been dropped and recreated.' );
        }

        $query = 'ALTER TABLE ' . $this->tableNamePrefixed . ' ';

        $count = 1;
        foreach ( $this->propertyProps as $propertyProp ) {
            if ( array_key_exists( $propertyProp->name, $currentColumns ) ) {
                // Column already present, create update query
                $query .= $this->helperQuery->getUpdateQueryForProperty( $propertyProp, true );
            } else {
                // Column is new, create add query
                $query .= $this->helperQuery->getUpdateQueryForProperty( $propertyProp, false );
            }
            $query .= $count < count( $this->propertyProps ) ? ',' : '';

            $count ++;
        }

        foreach ( $currentColumns as $column ) {
            $propertyName = $this->getPropertyNameByDbName( $column->COLUMN_NAME );
            if ( ! $propertyName || ! $this->hasField( $propertyName ) ) {
                // Column present in database but not in entity
                $nonExistingColumns[] = $column->COLUMN_NAME;
                if ( $removeNonExistingColumns ) {
                    $query .= ',' . $this->helperQuery->getRemoveQueryForProperty( $column->COLUMN_NAME );
                }
            }

            // Field has no index yet, but should have one
            if ($propertyName && !in_array( $column->COLUMN_KEY, array('MUL', 'UNI'), true ) && $this->hasField( $propertyName ) && in_array($column->COLUMN_NAME, $this->indexes, true)) {
                $indexesToAdd[] = $column->COLUMN_NAME;
            }
            // Field has index, but should not have one
            if ($propertyName && in_array( $column->COLUMN_KEY, array('MUL', 'UNI'), true ) && $this->hasField( $propertyName ) && !in_array($column->COLUMN_NAME, $this->indexes, true)) {
                $indexesToRemove[] = $column->COLUMN_NAME;
            }
        }

        if (!empty($indexesToAdd)) {
            foreach ($indexesToAdd as $index) {
                $query .= ', ADD INDEX(';
                $query .= $index;
                $query .= ')';
            }
        }
        if (!empty($indexesToRemove)) {
            foreach ($indexesToRemove as $index) {
                $query .= ', DROP INDEX ';
                $query .= $index;
            }
        }

        if ($this->tableName === 'user_settings') {
            var_dump($query);
        }

        $this->database->query( $query );

        return $nonExistingColumns;
    }

    /**
     * Method to get table columns for entity
     *
     * @return array|null   associative array of columns, null when table does not exist
     */
    private function getTableColumns(): ?array {
        $query = 'SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N\'' . $this->tableNamePrefixed . '\' AND TABLE_SCHEMA = N\'' . $this->database->getConfig( "database" ) . '\'';

        try {
            $columns = $this->database->query( $query );
        } catch ( \Dibi\Exception $exception ) {
            throw new DatabaseException( $exception->getMessage(), $exception->getCode(), $exception );
        }

        $columnsArr = array();
        foreach ( $columns as $column ) {
            if ( property_exists( $column, 'COLUMN_NAME' ) ) {
                $columnsArr[ $column->COLUMN_NAME ] = $column;
            }
            if ( property_exists( $column, 'column_name' ) ) {
                $column->COLUMN_NAME                = $column->column_name;
                $columnsArr[ $column->COLUMN_NAME ] = $column;
            }
        }

        return ! empty( $columnsArr ) ? $columnsArr : null;
    }


}