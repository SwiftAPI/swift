<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Logging\Entity;

use stdClass;
use Swift\Model\Attributes\Field;
use Swift\Model\Attributes\Table;
use Swift\Model\Entity;
use Swift\Model\Types\FieldTypes;
use Swift\Model\Types\Serialize;

/**
 * Class Log
 * @package Swift\Logging\Entity\Log
 */
#[Table(name: 'log')]
class LogEntity extends Entity {

	/**
	 * @var int $id
	 */
	#[Field( name: 'id', primary: true, type: FieldTypes::INT, length: 11 )]
	protected int $id;

	/**
	 * @var string  $channel
	 */
	#[Field(name: 'channel', type: FieldTypes::TEXT, length: 255)]
	protected string $channel;

	/**
	 * @var string  $message
	 */
	#[Field(name: 'message', type: FieldTypes::TEXT)]
	protected string $message;

    /**
     * @var int $level
     */
    #[Field(name: 'level', type: FieldTypes::INT, length: 11)]
	protected int $level;

    /**
     * @var string  $levelName
     */
    #[Field(name: 'level_name', type: FieldTypes::TEXT, length: 255)]
    protected string $levelName;

    /**
     * @var stdClass   $context
     */
    #[Field(name: 'context', type: FieldTypes::JSON, serialize: [Serialize::JSON])]
    protected stdClass $context;

    /**
     * @var \DateTime  $datetime
     */
    #[Field(name: 'datetime', type: FieldTypes::DATETIME, serialize: [Serialize::DATETIME])]
    protected \DateTime $datetime;

}