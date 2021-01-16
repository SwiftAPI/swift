<?php declare(strict_types=1);

namespace Honeywell\Model;

use Swift\GraphQl\Attributes\Type;
use Swift\Model\Entity;
use Swift\Model\Attributes\DB;
use Swift\Model\Attributes\DBField;
use Swift\Kernel\Attributes\DI;
use Swift\Model\Types\FieldTypes;
use stdClass;

/**
 * Class Thermostat
 * @package Honeywell\Model\Thermostat
 */
#[DI( name: 'honeywell.thermostat', shared: false ), DB(table: 'honeywell_device'), Type]
final class Thermostat extends Entity
{

	/**
	 * @var int $id
	 */
	#[DBField( name: 'id', primary: true, type: FieldTypes::INT, length: 11 )]
	protected int $id;

	/**
	 * @var stdClass  $state
	 */
	#[DBField(name: 'state', type: FieldTypes::TEXT, serialize: ['json'])]
	protected stdClass $state;

	/**
	 * @var string  $deviceID
	 */
	#[DBField(name: 'device_id', type: FieldTypes::TEXT, length: 255)]
	protected string $deviceID;

	/**
	 * @var int  $locationID
	 */
	#[DBField(name: 'location_id', type: FieldTypes::INT, length: 11)]
	protected int $locationID;

	/**
	 * @var stdClass $schedule
	 */
	#[DBField(name: 'schedule', type: FieldTypes::TEXT, serialize: ['json'])]
	protected stdClass $schedule;

	/**
	 * @var stdClass $settings
	 */
	#[DBField(name: 'settings', type: FieldTypes::TEXT, serialize: ['json'])]
	protected stdClass $settings;

}