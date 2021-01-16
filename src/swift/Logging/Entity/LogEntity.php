<?php declare(strict_types=1);

namespace Swift\Logging\Entity;

use stdClass;
use Swift\Model\Attributes\DB;
use Swift\Model\Attributes\DBField;
use Swift\Model\Entity;
use Swift\Model\Types\FieldTypes;
use Swift\Model\Types\Serialize;

/**
 * Class Log
 * @package Swift\Logging\Entity\Log
 */
#[DB(table: 'log')]
class LogEntity extends Entity {

	/**
	 * @var int $id
	 */
	#[DBField( name: 'id', primary: true, type: FieldTypes::INT, length: 11 )]
	protected int $id;

	/**
	 * @var string  $channel
	 */
	#[DBField(name: 'channel', type: FieldTypes::TEXT, length: 255)]
	protected string $channel;

	/**
	 * @var string  $message
	 */
	#[DBField(name: 'message', type: FieldTypes::TEXT)]
	protected string $message;

    /**
     * @var int $level
     */
    #[DBField(name: 'level', type: FieldTypes::INT, length: 11)]
	protected int $level;

    /**
     * @var string  $levelName
     */
    #[DBField(name: 'level_name', type: FieldTypes::TEXT, length: 255)]
    protected string $levelName;

    /**
     * @var stdClass   $context
     */
    #[DBField(name: 'context', type: FieldTypes::JSON, serialize: [Serialize::JSON])]
    protected stdClass $context;

    /**
     * @var string  $datetime
     */
    #[DBField(name: 'datetime', type: FieldTypes::DATETIME, serialize: [Serialize::DATETIME])]
    protected string $datetime;

}