<?php declare(strict_types=1);

namespace HoneywellOld\Model\Entity\Thermostat;

use Swift\GraphQl\Attributes\Type;
use Swift\Kernel\Attributes\DI;
use Swift\Model\Entity;
use Swift\Model\Attributes\DB;
use Swift\Model\Attributes\DBField;
use Swift\Model\Types\FieldTypes;
use stdClass;

/**
 * Class ScheduleItem
 * @package Honeywell\Model\Entity\Thermostat
 */
#[DB(table: 'honeywell_schedule_deprecated'), Type, DI(exclude: true)]
final class Schedule extends Entity
{

	/**
	 * @var int $id
	 */
	#[DBField( name: 'id', primary: true, type: FieldTypes::INT, length: 11 )]
	protected int $id;

	/**
	 * @var string  $title
	 */
	#[DBField(name: 'title', type: FieldTypes::TEXT, length: 255)]
	protected string $title;

	/**
	 * @var int $deviceID
	 */
	#[DBField(name: 'device_id', type: FieldTypes::INT, length: 11)]
	protected int $deviceID;

	/**
	 * @var string  $start
	 */
	#[DBField(name: 'start', type: FieldTypes::DATETIME, serialize: ['datetime'])]
	protected string $start;

	/**
	 * @var string  $end
	 */
	#[DBField(name: 'end', type: FieldTypes::DATETIME, serialize: ['datetime'])]
	protected string $end;

	/**
	 * @var bool  $geofenced
	 */
	#[DBField( name: 'geofenced', type: FieldTypes::INT, serialize: ['bool'], length: 11 )]
	protected bool $geofenced;

	/**
	 * @var float $temp
	 */
	#[DBField(name: 'temp', type: FieldTypes::FLOAT)]
	protected float $temp;

	/**
	 * @var float $geoAwayTemp
	 */
	#[DBField(name: 'geo_away_temp', type: FieldTypes::FLOAT)]
	protected float $geoAwayTemp;

	/**
	 * @var int $geoRadius
	 */
	#[DBField(name: 'geo_radius', type: FieldTypes::INT, length: 11)]
	protected int $geoRadius;

	/**
	 * @var string  $type
	 */
	#[DBField(name: 'type', type: FieldTypes::TEXT, length: 255)]
	protected string $type;

	/**
	 * @var stdClass  $params
	 */
	#[DBField(name: 'params', type: FieldTypes::LONGTEXT, serialize: ['json'])]
	protected stdClass $params;

	/**
	 * @var bool $state
	 */
	#[DBField(name: 'state', type: FieldTypes::INT, length: 11)]
	protected bool $state;

	/**
	 * @var string  $created
	 */
	#[DBField(name: 'created', type: FieldTypes::DATETIME, serialize: ['datetime'])]
	protected string $created;

}