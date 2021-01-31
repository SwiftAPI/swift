<?php declare(strict_types=1);

namespace Swift\Users\Model\Entity;

use Swift\Model\Entity;
use Swift\Model\Attributes\DB;
use Swift\Model\Attributes\DBField;
use Swift\Model\Types\FieldTypes;

/**
 * Class User
 * @package Swift\Users\Model\Entity
 */
#[DB(table: 'users')]
class User extends Entity {

	#[DBField( name: 'id', primary: true, type: FieldTypes::INT, length: 11 )]
	protected int $id;

	#[DBField(name: 'username', type: FieldTypes::TEXT, length: 128, unique: true)]
	protected string $username;

	#[DBField(name: 'first_name', type: FieldTypes::TEXT, length: 255)]
	protected string $firstName;

	#[DBField(name: 'last_name', type: FieldTypes::TEXT, length: 255)]
	protected string $lastName;

	#[DBField(name: 'email', type: FieldTypes::TEXT, length: 255, unique: true)]
	protected string $email;

	#[DBField(name: 'password', type: FieldTypes::TEXT, length: 255, index: true)]
	protected string $password;

}