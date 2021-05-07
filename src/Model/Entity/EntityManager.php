<?php declare(strict_types=1);
/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Entity;

use Swift\Database\DatabaseDriver;

abstract class EntityManager
{

	/**
	 * @var DatabaseDriver $database
	 */
	protected $database;

	/**
	 * @var $mainEntity
	 */
	protected $mainEntity;

	/**
	 * @var array $entities
	 */
	protected $entities = array();

	/**
	 * @var array $mapping
	 */
	protected $mapping = array();

	/**
	 * @var array $aliases
	 */
	protected $aliases = array();

	/**
	 * EntityManager constructor.
	 *
	 * @param DatabaseDriver $databaseDriver
	 */
	public function __construct(
		DatabaseDriver $databaseDriver
	) {
		$this->database     = $databaseDriver;
	}

	/**
	 * Method to populate entity state
	 *
	 * @param         $state
	 * @param string  $alias
	 * @param bool    $autoload
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function populateState($state, string $alias, bool $autoload = false) : void {
		if (!array_key_exists($alias, $this->aliases)) {
			throw new \Exception('Alias unknown');
		}

		$this->entities[$alias]->populateState($state, $autoload);

		if ($autoload) {
			$this->load();
		}
	}

	/**
	 * Method to validate query keys
	 *
	 * @param array $keys   [entityalias => propertyname|array of propertynames]
	 *
	 * @return bool
	 * @throws \Exception
	 */
	protected function validateQueryKeys(array $keys) : bool {
		foreach ($keys as $alias => $key) {
			$this->validateAliasExists($alias);

			if (is_array($key)) {
				foreach ($key as $item) {
					if (!$this->entities[$alias]->hasProperty($item)) {
						throw new \Exception('Property ' . $key . ' does not exist for ' . $alias, 500);
					}
				}
			} else {
				if (!$this->entities[$alias]->hasProperty($key)) {
					throw new \Exception('Property ' . $key . ' does not exist for ' . $alias, 500);
				}
			}
		}

		return true;
	}

	/**
	 * Method to export an entity object
	 *
	 * @param string $alias   entity alias of the entity to export
	 *
	 * @throws \Exception
	 */
	public function exportEntity(string $alias) {
		if (!array_key_exists($alias, $this->entities)) {
			throw new \Exception('Entity ' . $alias . ' is not found', 500);
		}

		return $this->entities[$alias];
	}

	/**
	 * Method to set main entity
	 *
	 * @param $entity
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function setMainEntity($entity, string $alias = '') : void {
		$alias = $alias ? $alias : get_class($entity);
		$this->validateEntity($entity);

		if ($this->mainEntity) {
			throw new \Exception('Main entity allready set this main not be modified. Perform a reset()', 500);
		}

		$this->mainEntity                   = $alias;
		$this->entities[$alias]             = $entity;
		$this->aliases[$alias]              = get_class($entity);
	}

	/**
	 * Method to join entity to main entity
	 *
	 * @param        $entity
	 * @param string $alias
	 * @param string $joinKey
	 * @param string $mainKey
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function joinEntity($entity, string $alias, string $joinKey, string $mainKey) : void {
		$alias = $alias ?: get_class( $entity );
		$this->validateJoin($entity, $alias, $joinKey, $mainKey);

		$this->entities[$alias] = $entity;
		$this->aliases[$alias] = get_class($entity);
		$this->mapping[$alias] = array(
				'mainKey' =>  $mainKey,
				'joinKey' =>  $joinKey,
		);
	}

	/**
	 * Method to validate if given entity is as expected
	 *
	 * @param $entity
	 *
	 * @return bool       true on valid. Exception on invalid
	 * @throws \Exception
	 */
	protected function validateEntity($entity) : bool {
		if (! is_subclass_of($entity, 'Swift\Model\Entity\Entity') ) {
			throw new \Exception('Given class is not an entity', 500);
		}

		return true;
	}

	/**
	 * Method to validate whether an alias does exist
	 *
	 * @param string $alias
	 *
	 * @return bool       true on alias exists. exception on alias does not exist
	 * @throws \Exception
	 */
	protected function validateAliasExists(string $alias) : bool {
		if (!array_key_exists($alias, $this->aliases)) {
			throw new \Exception('Alias ' . $alias . ' does not exist', 500);
		}

		return true;
	}

	/**
	 * Method to validate of a join may be done
	 *
	 * @param        $entity
	 * @param string $alias
	 * @param string $joinKey
	 * @param string $mainKey
	 *
	 * @return bool       true on join may be done. exception on validation failed
	 * @throws \Exception
	 */
	protected function validateJoin($entity, string $alias, string $joinKey, string $mainKey) : bool {
		$this->validateEntity($entity);

		if (!isset($this->mainEntity)) {
			throw new \Exception('Main entity missing', 500);
		}

		if (array_key_exists(get_class($entity), $this->entities)) {
			throw new \Exception('Entity allready added', 500);
		}

		if (!property_exists($this->entities[$this->mainEntity], $mainKey)) {
			throw new \Exception('Main key does not exist', 500);
		}

		if (!property_exists($entity, $joinKey)) {
			throw new \Exception('Join key does not exist', 500);
		}

		if ($alias && array_key_exists($alias, $this->entities)) {
			throw new \Exception('Alias is not unique', 500);
		}

		return true;
	}

	/**
	 * Method to reset class
	 *
	 * @return void
	 */
	public function reset() : void {
		$this->mainEntity = '';
		$this->entities   = array();
		$this->mapping    = array();
		$this->aliases    = array();
	}


}