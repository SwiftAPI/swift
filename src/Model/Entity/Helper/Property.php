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
use Swift\Model\Types\FieldTypes;

/**
 * Class Property
 * @package Swift\Model\Entity\Helper
 */
#[Autowire]
class Property {

	/**
	 * Method to get property query
	 *
	 * @param $property
	 *
	 * @return string
	 */
	public function getPropertyQuery($property) : string {
		$propertyString = $property->name;

		$propertyString = $this->appendTypeAndLenght($property, $propertyString);
		$propertyString = $this->appendOther($property, $propertyString);

		return $propertyString;
	}

	/**
	 * Method to generate type and (optional) length property
	 *
	 * @param stdClass $property
	 * @param string    $propertyString
	 *
	 * @return string
	 */
	private function appendTypeAndLenght( stdClass $property, string $propertyString) : string {
        $propertyString .= ' ' . $property->type;

        $property->length = (($property->type === FieldTypes::TEXT) && ($property->length < 1)) ? 255 : $property->length;
        $property->length = (($property->type === FieldTypes::INT) && ($property->length < 1)) ? 11 : $property->length;


		if ( ( $property->length > 0) && in_array( $property->type, array( FieldTypes::TEXT, FieldTypes::INT ), true )) {
			$propertyString .= '(' . $property->length . ')';
		}

		return $propertyString;
	}

	/**
	 * Method to append several SQL options
	 *
	 * @param stdClass $property
	 * @param string    $propertyString
	 *
	 * @return string
	 */
	private function appendOther( stdClass $property, string $propertyString) : string {
		if (!$property->empty) {
			$propertyString .= ' NOT NULL';
		}

		if ($property->primary && (($property->type === FieldTypes::INT) || ($property->type === FieldTypes::FLOAT))) {
			$propertyString .= ' AUTO_INCREMENT';
		}

		if (!$property->primary && $property->unique) {
		    $propertyString .= ' UNIQUE';
        }

		return $propertyString;
	}
}