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

	/**
	 * @var int $id
	 */
	#[DBField( name: 'id', primary: true, type: FieldTypes::INT, length: 11 )]
	protected int $id;

	/**
	 * @var string  $username
	 */
	#[DBField(name: 'username', type: FieldTypes::TEXT, length: 128)]
	protected string $username;

	/**
	 * @var string  $name
	 */
	#[DBField(name: 'name', type: FieldTypes::TEXT, length: 255)]
	protected string $name;

	/**
	 * @var string  $email
	 */
	#[DBField(name: 'email', type: FieldTypes::TEXT, length: 255)]
	protected string $email;

	/**
	 * @var string  $password
	 */
	#[DBField(name: 'password', type: FieldTypes::TEXT, length: 255)]
	protected string $password;

}