<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\Entity;

use Swift\GraphQl\Attributes\Type;
use Swift\Kernel\Attributes\DI;
use Swift\Model\Entity;
use Swift\Model\Attributes\DB;
use Swift\Model\Attributes\DBField;
use Swift\Model\EntityInterface;
use Swift\Model\Types\FieldTypes;
use Swift\Security\User\UserStorageInterface;

/**
 * Class UserEntity
 * @package Swift\Security\User\Entity
 */
#[DI(aliases: [EntityInterface::class . ' $userEntity', UserStorageInterface::class . ' $userDatabaseStorage']), DB(table: 'users'), Type]
class UserEntity extends Entity implements UserStorageInterface {

	#[DBField( name: 'id', primary: true, type: FieldTypes::INT, length: 11 )]
	protected int $id;

	#[DBField(name: 'username', type: FieldTypes::TEXT, length: 128, unique: true)]
	protected string $username;

	#[DBField(name: 'first_name', type: FieldTypes::TEXT, length: 255)]
	protected string $firstname;

	#[DBField(name: 'last_name', type: FieldTypes::TEXT, length: 255)]
	protected string $lastname;

	#[DBField(name: 'email', type: FieldTypes::TEXT, length: 255, unique: true)]
	protected string $email;

	#[DBField(name: 'password', type: FieldTypes::TEXT, length: 255, index: true)]
	protected string $password;

	#[DBField(name: 'created', type: FieldTypes::DATETIME)]
	protected \DateTime $created;

	#[DBField(name: 'modified', type: FieldTypes::DATETIME)]
	protected \DateTime $modified;

}