<?php declare(strict_types=1);

namespace HoneywellOld\Model\Entity\Thermostat;

use Swift\Kernel\Attributes\DI;
use Swift\Model\Entity;
use Swift\Model\Attributes\DB;
use Swift\Model\Attributes\DBField;
use Swift\Model\Types\FieldTypes;
use Swift\GraphQl\Attributes\Type;

/**
 * Class LogThermostat
 * @package Honeywell\Model\Entity
 */
#[DB(table: 'honeywell_log_device'), Type, DI(exclude: true)]
final class LogThermostat extends Entity {

	/**
	 * @var int $id
	 */
	#[DBField( name: 'id', primary: true, type: FieldTypes::INT, length: 11 )]
	protected int $id;

	/**
	 * @var int $deviceID
	 */
	#[DBField(name: 'device_id', type: FieldTypes::INT, length: 11)]
	protected int $deviceID;

	/**
	 * @var bool  $heating
	 */
	#[DBField(name: 'heating', type: FieldTypes::INT, serialize: ['bool'], length: 11)]
	protected bool $heating;

	/**
	 * @var float $setTemp
	 */
	#[DBField(name: 'set_temp', type: FieldTypes::FLOAT)]
	protected float $setTemp;

	/**
	 * @var float $indoorTemp
	 */
	#[DBField(name: 'indoor_temp', type: FieldTypes::FLOAT)]
	protected float $indoorTemp;

	/**
	 * @var bool  $occupated
	 */
	#[DBField(name: 'occupated', type: FieldTypes::INT, serialize: ['bool'], length: 11)]
	protected bool $occupated;

	/**
	 * @var string  $date
	 */
	#[DBField(name: 'date', type: FieldTypes::DATETIME, serialize: ['datetime'])]
	protected string $date;

	/**
	 * @var string  $time
	 */
	#[DBField(name: 'time', type: FieldTypes::TEXT, serialize: ['time'], length: 128)]
	protected string $time;

}