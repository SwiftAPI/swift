<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Logging\Repository;

use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use stdClass;

/**
 * Class Log
 * @package Swift\Logging\Repository\Log
 */
#[Entity, Table(name: 'log')]
class LogEntity {

	/**
	 * @var int $id
	 */
	#[Id, GeneratedValue, Column( name: 'id', type: 'integer', length: 11 )]
	protected int $id;

	/**
	 * @var string  $channel
	 */
	#[Column(name: 'channel', type: 'string', length: 255)]
	protected string $channel;

	/**
	 * @var string  $message
	 */
	#[Column(name: 'message', type: 'string')]
	protected string $message;

    /**
     * @var int $level
     */
    #[Column(name: 'level', type: 'integer', length: 11)]
	protected int $level;

    /**
     * @var string  $levelName
     */
    #[Column(name: 'level_name', type: 'string', length: 255)]
    protected string $levelName;

    /**
     * @var stdClass   $context
     */
    #[Column(name: 'context', type: 'json_array')]
    protected stdClass $context;

    /**
     * @var DateTimeInterface  $datetime
     */
    #[Column(name: 'datetime', type: 'datetime')]
    protected DateTimeInterface $datetime;

}