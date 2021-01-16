<?php declare(strict_types=1);

namespace Honeywell\Model;

use Honeywell\Types\ConditionTypeEnum;
use stdClass;
use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\Type;
use Swift\Model\Entity;
use Swift\Model\Attributes\DB;
use Swift\Model\Attributes\DBField;
use Swift\Model\Types\FieldTypes;
use Swift\Model\Types\Serialize;

/**
 * Class Schedule
 * @package Honeywell\Model\Thermostat
 */
#[DB(table: 'honeywell_conditions'), Type]
final class Condition extends Entity
{

	#[DBField( name: 'id', primary: true, type: FieldTypes::INT, length: 11 )]
	private int $id;

	#[DBField( name: 'title', type: FieldTypes::TEXT, length: 255)]
	private string $title;

	#[DBField( name: 'type', type: FieldTypes::TEXT, enum: ConditionTypeEnum::class ), Field(type: ConditionTypeEnum::class)]
	private string $type;

	#[DBField(name: 'temp', type: FieldTypes::FLOAT)]
	private float $temp;

	#[DBField(name: 'weight', type: FieldTypes::INT, index: true)]
	private int $weight;

	#[DBField(name: 'state', type: FieldTypes::INT, length: 2, index: true)]
	private int $state;

	#[DBField(name: 'rules', type: FieldTypes::JSON, serialize: [Serialize::JSON])]
	private stdClass $rules;

	#[DBField(name: 'created', type: FieldTypes::DATETIME, serialize: [Serialize::DATETIME])]
	private string $created;

	#[DBField(name: 'modified', type: FieldTypes::TEXT, serialize: [Serialize::DATETIME])]
	private string $modified;

}