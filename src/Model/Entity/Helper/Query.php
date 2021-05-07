<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Entity\Helper;

use stdClass;
use Swift\Kernel\Attributes\Autowire;

/**
 * Class Query
 * @package Swift\Model\Entity\Helper
 */
#[Autowire]
class Query {

	/**
	 * @var Property    $propertyHelper
	 */
	private $propertyHelper;

	/**
	 * Query constructor.
	 *
	 * @param Property $propertyHelper
	 */
	public function __construct(
		Property    $propertyHelper
	) {
		$this->propertyHelper   = $propertyHelper;
	}

	/**
	 * Method to create SQL string to be use to create a table row
	 *
	 * @param stdClass $property
	 *
	 * @return string
	 */
	public function getCreateQueryForProperty( stdClass $property) : string {
		return $this->propertyHelper->getPropertyQuery($property);
	}

	/**
	 * Method to get update query for property
	 *
	 * @param stdClass $property
	 * @param bool      $isUpdate
	 *
	 * @return string
	 */
	public function getUpdateQueryForProperty( stdClass $property, bool $isUpdate) : string {
		return $isUpdate ?
            'MODIFY COLUMN ' . $this->propertyHelper->getPropertyQuery($property) :
            'ADD ' . $this->propertyHelper->getPropertyQuery($property);
    }

	/**
	 * Method to get remove query
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function getRemoveQueryForProperty(string $name) : string {
		return 'DROP COLUMN ' . $name;
	}
}