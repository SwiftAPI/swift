<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Entity;

use Swift\Database\DatabaseDriver;
use Swift\Kernel\Attributes\Autowire;

/**
 * Class EntityManagerList
 * @package Swift\Model\Entity
 */
#[Autowire]
class EntityManagerList extends EntityManager {

	/**
	 * EntityManager constructor.
	 *
	 * @param DatabaseDriver $databaseDriver
	 */
	public function __construct(
		DatabaseDriver $databaseDriver
	) {
		parent::__construct($databaseDriver);
	}

	/**
	 * Method to populate entity state
	 *
	 * @param         $state
	 * @param string  $alias
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function populateStateFromDB($state, string $alias) : void {
		if (!array_key_exists($alias, $this->aliases)) {
			throw new \Exception('Alias unknown');
		}

		$this->entities[$alias]->populateStateFromDB($state);
	}

	public function getList(array $keys = array(), bool $usePrimaryKeyValueAsArrayKey = false) : array {
		if (!empty($keys)) {
			$this->validateQueryKeys($keys);
		}

		$mainEntityPrimaryKey = $this->entities[$this->mainEntity]->get('primaryKey');

		$query = $this->database->select('*');
		$query->from('[' . $this->entities[$this->mainEntity]->getTableName() . ']')->as($this->mainEntity);

		// Create joins
		if (!empty($this->mapping)) {
			foreach ($this->mapping as $key => $map) {
				// Loop through entities and add their states to where clause
				$query->innerJoin('[' . $this->entities[$key]->getTableName() . ']')->as($key)->on('[' . $this->mainEntity . '.' . $map['mainKey'] . '] =', '[' . $key . '.' . $map['joinKey'] . ']');
			}
		}

		if (!empty($keys)) {
			foreach ($this->entities as $entityAlias => $entity) {
				// Set all entity values as where clause
				$values = $this->entities[$entityAlias]->getValuesAsArray(true); // true = perform onBeforeSave actions

				if (empty($values)) {
					continue;
				}

				foreach ($values as $propertyName => $value) {
					if (is_null($value)) {
						continue;
					}

					$where = $this->entities[$entityAlias]->getPropertyWhereClause($propertyName);
					$query->where($where['prepare'], $where['value']);
				}
			}
		}

		$result = $query->fetchAll();
		$resultNew = array();

		foreach ($result as $item) {
			$this->populateStateFromDB($item, $this->mainEntity);
			$data = $this->entities[$this->mainEntity]->getValuesAsObject();
			if ($usePrimaryKeyValueAsArrayKey) {
				$item = $resultNew[$data->{$mainEntityPrimaryKey}] = $data;
			} else {
				array_push($resultNew, $data);
			}
		}

		return $resultNew;
	}


}