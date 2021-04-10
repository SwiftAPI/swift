<?php declare(strict_types=1);

namespace Swift\Model\Entity;

use Exception;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Pure;
use ReflectionClass;
use RuntimeException;
use stdClass;
use Swift\Database\DatabaseDriver;
use Swift\Events\EventDispatcher;
use Swift\Kernel\DiTags;
use Swift\Model\Attributes\DB;
use Swift\Model\Attributes\DBField;
use Swift\Model\Entity\Helper\Query;
use Swift\Model\Events\EntityOnFieldSerializeEvent;
use Swift\Model\Events\EntityOnFieldUnSerializeEvent;
use Swift\Model\Exceptions\DatabaseException;
use Swift\Model\Exceptions\InvalidConfigurationException;
use Swift\Model\Exceptions\NoResultException;
use Swift\Model\Types\FieldTypes;
use Swift\Kernel\Attributes\DI;

/**
 * Class Entity
 * @package Swift\Model\Entity
 *
 * @deprecated
 */
#[DI(tags: [DiTags::ENTITY]), Deprecated(reason: 'Deprecated in favor of version without persistent state', replacement: \Swift\Model\Entity::class)]
abstract class Entity
{

	/** @var DatabaseDriver $database */
	protected DatabaseDriver $database;

	/** @var EventDispatcher $dispatcher */
	protected EventDispatcher $dispatcher;

	/** @var Query $helperQuery */
	protected Query $helperQuery;

	/** @var ReflectionClass $reflectionClass  Reflection of this class */
	protected ReflectionClass $reflectionClass;

	/** @var string $primaryKey the primary key in the table */
	protected string $primaryKey;

	/** @var array $propertyMap  map of properties and their belonging name in the table */
	protected array $propertyMap = array();

	/** @var array $propertyActions  actions to related to properties */
	protected array $propertyActions = array();

	/** @var array $propertyProps  properties and their props/settings */
	protected array $propertyProps = array();

	/** @var string $tableName  table name without prefix */
	protected string $tableName;

	/** @var string $tableNamePrefixed  prefixed version of the table name */
	protected string $tableNamePrefixed;

    /**
     * Entity constructor.
     *
     * @param DatabaseDriver $databaseDriver
     * @param EventDispatcher $dispatcher
     * @param Query $helperQuery
     *
     * @throws Exception
     */
	public function __construct(
			DatabaseDriver $databaseDriver,
			EventDispatcher $dispatcher,
			Query $helperQuery
	) {
		$this->database         = $databaseDriver;
		$this->dispatcher       = $dispatcher;
		$this->helperQuery      = $helperQuery;
		$this->reflectionClass  = new ReflectionClass(static::class);

		$this->setTable();
		$this->mapProperties();
	}

    public function findOne( array|stdClass $state, bool $exceptionOnNotFound = false ): ?stdClass {
        $result = $this->find($state, new Arguments(limit: 1), $exceptionOnNotFound);

        return $result[0] ?? null;
	}

    public function find( array|stdClass $state, Arguments|null $arguments = null, bool $exceptionOnNotFound = false ): array {
        $state = (array) $state;
        $query = $this->database->select('[*]')->from('[' . $this->tableNamePrefixed . ']');

        foreach ($state as $propertyName => $value) {
            if (!isset($this->{$propertyName})) {
                continue;
            }

            $value = $this->onBeforeSave($value, $propertyName);

            $query->where($this->getPropertyDBName($propertyName) . ' = %s', $value);
        }

        if ($arguments) {
            $arguments->apply($query, $this->propertyMap, $this->primaryKey);
        }

        $results = $query->fetchAll();

        if ($exceptionOnNotFound && is_null($results)) {
            throw new NoResultException(sprintf('No result found for search in %s', __CLASS__));
        }

        $items = array();
        foreach ($results as $result) {
            $item = new stdClass();
            foreach ($result->toArray() as $key => $value) {
                $property = array_search( $key, $this->propertyMap, true );
                if ($property && property_exists($this, $property)) {
                    $item->{$property} = $this->onAfterLoad($value, $property);
                }
            }
            $items[] = $item;
        }

        return $items;
	}

	/**
	 * Method to populate object state
	 *
	 * @param array|stdClass  $state      state values to populate
	 * @param bool            $autoload   whether to try to autoload the object based on the given data
	 *
	 * @return void
     * @deprecated
	 */
	public function populateState(array|stdClass $state, bool $autoload): void {
		if (!is_array($state)) {
			$state = (array) $state;
		}

		foreach ($state as $propertyName => $value) {
			// Check if property is mapped for db usage
			if (!$this->hasProperty($propertyName)) {
				continue;
			}

			$this->{$propertyName} = $value;
		}

		if ($autoload) {
			$this->load();
		}
	}

	/**
	 * Method to populate state from db values
	 *
	 * @param $state
	 *
	 * @return void
	 */
	public function populateStateFromDB($state): void {
		if (!is_array($state)) {
			$state = (array) $state;
		}

		foreach ($state as $key => $value) {
			$property = array_search( $key, $this->propertyMap, true );
			if ($property && property_exists($this, $property)) {
				$value = $this->onAfterLoad($value, $property);
				$this->{$property} = $value;
			}
		}
	}

    /**
     * Method to load state from db based on populated properties
     *
     * @param array $keys keys to load
     * @param array $loadBy keys to use a reference for load
     * @param bool $exceptionOnNotFound
     *
     * @return void
     */
	public function load(array $keys = array(), array $loadBy = array(), bool $exceptionOnNotFound = false): void {
		$query = $this->database->select('[*]')->from('[' . $this->tableNamePrefixed . ']');

		foreach ($this->propertyMap as $propertyName => $dbKey) {
			if (!isset($this->{$propertyName})) {
				continue;
			}

			$value = $this->onBeforeSave($this->{$propertyName}, $propertyName);

			$query->where($dbKey . ' = %s', $value);
		}

		$result = $query->fetch();
		$result = $result->toArray();

		if ($exceptionOnNotFound && is_null($result)) {
		    throw new NoResultException(sprintf('No result found for search in %s', __CLASS__));
        }

		$this->populateStateFromDB($result);
	}

    /**
     * Method to save/update based on the current state
     *
     * @return void
     */
	public function save(): void {
		// Check if record is new
		$isNew = ! ( isset( $this->{$this->primaryKey} ) && $this->{$this->primaryKey} );

		try {
            if ($isNew) { // Insert
                $this->database->query('INSERT INTO ' . $this->tableNamePrefixed, $this->getValuesForDatabase());
                if ($this->database->getInsertId()) {
                    $this->{$this->primaryKey} = $this->database->getInsertId();
                }
            } else { // Update
                $this->database->query(
                    'UPDATE ' . $this->tableNamePrefixed . ' SET',
                    $this->getValuesForDatabase(),
                    'WHERE ' . $this->primaryKey . ' = ?', $this->{$this->primaryKey}
                );
            }
        } catch (\Dibi\Exception $exception) {
		    throw new DatabaseException($exception->getMessage(), $exception->getCode(), $exception);
        }
	}

    /**
     * Method to delete a row from the database
     *
     * @return void
     */
	public function delete(): void {
		if (!isset($this->{$this->primaryKey}) || !$this->{$this->primaryKey}) {
			throw new DatabaseException('No item found to remove', 500);
		}

		try {
            $this->database->query(
                'DELETE FROM ' . $this->tableNamePrefixed . ' 
					WHERE ' . $this->primaryKey . ' = ' . $this->{$this->primaryKey}
            );
        } catch (\Dibi\Exception $exception) {
		    throw new DatabaseException($exception->getMessage(), $exception->getCode(), $exception);
        }
	}

    /**
     * Method to get all properties as array
     *
     * @param bool $preparedForSave
     *
     * @return array
     */
	public function getValuesAsArray(bool $preparedForSave = false): array {
		$values = array();
		foreach ($this->propertyMap as $propertyName => $dbBinding) {
			$value = $preparedForSave && $this->{$propertyName} ? $this->onBeforeSave($this->{$propertyName}, $propertyName) : $this->{$propertyName};
			$values[$propertyName] = $value;
		}

		return $values;
	}

    /**
     * @param bool $preparedForSave
     *
     * @return stdClass
     */
	public function getValuesAsObject(bool $preparedForSave = false): stdClass {
		$values = new stdClass();
		foreach ($this->propertyMap as $propertyName => $dbBinding) {
			$value = $preparedForSave && $this->{$propertyName} ? $this->onBeforeSave($this->{$propertyName}, $propertyName) : $this->{$propertyName};
			$values->{$propertyName} = $value;
		}

		return $values;
	}

	/**
	 * Method to get all properties as array with db bindings as key
	 *
	 * @return array
	 */
	protected function getValuesForDatabase(): array {
		$values = array();
		foreach ($this->propertyMap as $propertyName => $dbBinding) {
			$value = $this->{$propertyName} ?? null;
			$value = $this->onBeforeSave($value, $propertyName);

			$values[$dbBinding] = $value;
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
	protected function onBeforeSave($value, $propertyName): mixed {
		if (array_key_exists($propertyName, $this->propertyActions['serialize'])) {
			foreach ($this->propertyActions['serialize'][$propertyName] as $action) {
			    /** @var EntityOnFieldSerializeEvent $response */
			    $response = $this->dispatcher->dispatch(new EntityOnFieldSerializeEvent(entity: $this, action: $action, name: $propertyName, value: $value));
			    $value = $response->value;
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
	protected function onAfterLoad($value, $propertyName): mixed {
		if (array_key_exists($propertyName, $this->propertyActions['serialize'])) {
			foreach ($this->propertyActions['serialize'][$propertyName] as $action) {
			    /** @var EntityOnFieldUnSerializeEvent $response */
				$response = $this->dispatcher->dispatch(new EntityOnFieldUnSerializeEvent(entity: $this, action: $action, name: $propertyName, value: $value));
				$value = $response->value;
			}
		}

		return $value;
	}

	/**
	 * Method to get property where clause
	 *
	 * @param string $propertyName  name of the property to get
	 *
	 * @return array
	 */
	#[ArrayShape( [ 'prepare' => "string", 'value' => "mixed" ] )]
    public function getPropertyWhereClause( string $propertyName): array {
		if (!$this->hasProperty($propertyName)) {
			throw new InvalidArgumentException('Property ' . $propertyName . ' not found', 500);
		}

		return array(
				'prepare' =>  $this->propertyMap[$propertyName] . ' = %s ',
				'value'   =>  $this->{$propertyName}
		);
	}

	/**
	 * Method to validate if a property is available
	 *
	 * @param string $property
	 *
	 * @return bool
	 */
	#[Pure] public function hasProperty( string $property): bool {
		return array_key_exists($property, $this->propertyMap);
	}

	/**
	 * Method to get property from object
	 *
	 * @param string $property
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function get(string $property): mixed {
		// Only this class itself or EntityManager are allowed access
		$calling_class = debug_backtrace(1, 1)[0]['class'];
		if ( !is_a($calling_class, EntityManager::class, true) &&
             !is_a($calling_class, __CLASS__, true)) {
			throw new RuntimeException('Access to method not allowed', 500);
		}

		if (!property_exists($this, $property)) {
			throw new InvalidArgumentException('Property not found', 500);
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
	public function getPropertyDBName(string $property): string {
		// Only this class itself or EntityManager are allowed access
		$calling_class = debug_backtrace(1, 1)[0]['class'];
		if ( !is_a($calling_class, EntityManager::class, true) &&
             !is_a($calling_class, __CLASS__, true)) {
			throw new RuntimeException('Access to method not allowed', 500);
		}

		if (!$this->hasProperty($property)) {
			throw new InvalidArgumentException('Property ' . $property . ' does not exist for ' . get_class($this), 500);
		}

		return $this->propertyMap[$property];
	}

	/**
	 * Populate table name from DB attribute
	 *
	 * @return void
	 */
	protected function setTable(): void {
        $annotations = $this->reflectionClass->getAttributes(name: DB::class);

        if (empty($annotations)) {
            throw new InvalidConfigurationException(sprintf('Entity %s missing DB attribute, this is an invalid use case. Please add %s attribute to class', static::class, DB::class));
        }

        $annotation = $annotations[0]->getArguments();

		if (empty($annotation['table'])) {
            throw new InvalidConfigurationException(sprintf('Entity %s is missing valid %s attribute configuration', static::class, DB::class));
		}

        $this->tableName = $annotation['table'];
        $this->tableNamePrefixed = $this->database->getPrefix() . $this->tableName;
	}

    /**
     * Method to get table name
     *
     * @param bool $prefixed
     *
     * @return string
     */
	public function getTableName(bool $prefixed = true): string {
		return $prefixed ? $this->tableNamePrefixed : $this->tableName;
	}

	/**
	 * Method to map object properties to table columns
	 *
	 * @return void
	 */
	protected function mapProperties() : void {
		$this->propertyActions['serialize'] = array();
		$properties = $this->reflectionClass->getProperties();
		foreach ($properties as $property) {
			$annotation = !empty($property->getAttributes( name: DBField::class )) ? $property->getAttributes( name: DBField::class )[0]->getArguments() : null;

			if ( empty($annotation) ) {
			    continue;
            }

			$annotation = (object) $annotation;

			if ( isset( $property->name, $annotation->name ) ) {
				$this->propertyMap[$property->name] = $annotation->name;

				$propertyProps              = new stdClass();
				$propertyProps->name        = $annotation->name;
				$propertyProps->type        = $annotation->type ?? FieldTypes::TEXT;
				$propertyProps->length      = $annotation->length ?? 0;
				$propertyProps->primary     = $annotation->primary ?? false;
				$propertyProps->serialize   = $annotation->serialize ?? array();
				$propertyProps->empty       = $annotation->empty ?? false;
				$propertyProps->unique      = $annotation->unique ?? false;
				$propertyProps->enum        = $annotation->enum ?? null;
				$this->propertyProps[$property->name] = $propertyProps;
			}

			if ( isset( $property->name, $annotation->serialize ) && $annotation && ! empty( $annotation->serialize ) ) {
				// Set serialize actions
				$this->propertyActions['serialize'][$property->name] = $annotation->serialize;
			}

			// Check for primary key
			if ( isset( $property->name, $annotation->primary ) && $annotation ) {
				if ($annotation->primary && isset($this->primaryKey)) {
					throw new InvalidConfigurationException('Multiple primary keys found');
				}

				if ($annotation->primary) {
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
	#[Pure] private function getPropertyNameByDbName( string $dbName) : ?string {
		$property = array_search( $dbName, $this->propertyMap, true );

		return ($property && property_exists($this, $property)) ? $property : null;
	}

	/**
	 * Method to reset properties
	 */
	public function reset() : void {
		foreach ($this->propertyMap as $propertyName => $dbName) {
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
	public function createTable(bool $dropTableIfExists = false) : bool {
		if ($dropTableIfExists) {
			$this->database->query('DROP TABLE if EXISTS ' . $this->tableNamePrefixed);
		}

		$query = 'CREATE TABLE ' . $this->tableNamePrefixed . ' (';
		$count = 1;
		foreach ($this->propertyProps as $propertyProp) {
			$query .= $this->helperQuery->getCreateQueryForProperty($propertyProp);
			$query .= $count < count($this->propertyProps) ? ',' : '';
			$count++;
		}
		if ($this->primaryKey) {
			$query .= ',PRIMARY KEY (' . $this->propertyMap[$this->primaryKey] . ')';
		}
		$query .= ' );';

		$this->database->query($query);

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
	public function updateTable(bool $removeNonExistingColumns, bool $dropTableIfExists) : array {
        $currentColumns         = $this->getTableColumns();
		$nonExistingColumns     = array();

		if (is_null($currentColumns)) {
			// Table does not exist yet
			$this->createTable(false);
			throw new DatabaseException('Table ' . $this->tableNamePrefixed . ' did not exist yet. It has been created.');
		}
		if ( $dropTableIfExists ) {
            // Table dropped and newly created
            $this->createTable(false);
            throw new DatabaseException('Table ' . $this->tableNamePrefixed . ' has been dropped and recreated.');
        }

		$query = 'ALTER TABLE ' . $this->tableNamePrefixed . ' ';

		$count = 1;
		foreach ($this->propertyProps as $propertyProp) {
			if (array_key_exists($propertyProp->name, $currentColumns)) {
				// Column already present, create update query
				$query .= $this->helperQuery->getUpdateQueryForProperty($propertyProp, true);
			} else {
				// Column is new, create add query
				$query .= $this->helperQuery->getUpdateQueryForProperty($propertyProp, false);
			}
			$query .= $count < count($this->propertyProps) ? ',' : '';
			$count++;
		}

		foreach ($currentColumns as $column) {
			$propertyName   = $this->getPropertyNameByDbName($column->COLUMN_NAME);
			if (!$propertyName || !$this->hasProperty($propertyName)) {
				// Column present in database but not in entity
				$nonExistingColumns[] = $column->COLUMN_NAME;
				if ($removeNonExistingColumns) {
					$query .= ',' . $this->helperQuery->getRemoveQueryForProperty($column->COLUMN_NAME);
				}
			}
		}

		$this->database->query($query);

		return $nonExistingColumns;
	}

	/**
	 * Method to get table columns for entity
	 *
	 * @return array|null   associative array of columns, null when table does not exist
	 * @throws \Dibi\Exception
	 */
	private function getTableColumns() : ?array {
		$query = 'SELECT column_name, data_type, column_type FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N\'' . $this->tableNamePrefixed . '\' AND TABLE_SCHEMA = N\'' . $this->database->getConfig("database") . '\'';
		$columns    = $this->database->query($query);

		$columnsArr = array();
		foreach ($columns as $column) {
		    if (property_exists($column, 'COLUMN_NAME')) {
                $columnsArr[$column->COLUMN_NAME]   = $column;
            }
            if (property_exists($column, 'column_name')) {
                $column->COLUMN_NAME = $column->column_name;
                $columnsArr[$column->COLUMN_NAME]   = $column;
            }
		}

		return !empty($columnsArr) ? $columnsArr : null;
	}


}