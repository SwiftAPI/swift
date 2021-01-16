<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Authentication\Model\Entity;

use Swift\Authentication\Types\AuthenticationLevelsEnum;
use Swift\Model\Entity;
use Swift\Model\Attributes\DB;
use Swift\Model\Attributes\DBField;
use Swift\Model\Types\FieldTypes;

/**
 * Class Token
 * @package Swift\Users\Model\Entity
 */
#[DB(table: 'token')]
class Token extends Entity {

	/**
	 * @var int $id
	 */
	#[DBField( name: 'id', primary: true, type: FieldTypes::INT, length: 11 )]
	protected int $id;

	/**
	 * @var string  $value
	 */
	#[DBField(name: 'value', type: FieldTypes::TEXT, length: 255)]
	protected string $value;

	/**
	 * @var string  $expirationDate
	 */
	#[DBField(name: 'expiration', type: FieldTypes::DATETIME, serialize: ['datetime'])]
	protected string $expirationDate;

	/**
	 * @var int  $clientID
	 */
	#[DBField(name: 'client_id', type: FieldTypes::INT, length: 11)]
	protected int $clientID;

	/**
	 * @var int  $userID
	 */
	#[DBField(name: 'user_id', type: FieldTypes::INT, length: 11)]
	protected int $userID;

	/**
	 * @var string  $level
	 */
	#[DBField(name: 'level', type: FieldTypes::TEXT, length: 255, enum: AuthenticationLevelsEnum::class)]
	protected string $level;

}