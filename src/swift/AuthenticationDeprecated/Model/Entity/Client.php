<?php declare(strict_types=1);

namespace Swift\AuthenticationDeprecated\Model\Entity;

use Swift\Model\Entity;
use Swift\Model\Attributes\DB;
use Swift\Model\Attributes\DBField;
use Swift\Model\Types\FieldTypes;

/**
 * Class Client
 * @package Swift\Users\Model\Entity
 */
#[DB(table: 'clients')]
class Client extends Entity {

	/**
	 * @var int $id
	 */
	#[DBField( name: 'id', primary: true, type: FieldTypes::INT, length: 11 )]
	protected int $id;

	/**
	 * @var string  $apikey
	 */
	#[DBField(name: 'apikey', type: FieldTypes::TEXT, length: 128)]
	protected string $apikey;

	/**
	 * @var string  $domain
	 */
	#[DBField(name: 'domain', type: FieldTypes::TEXT, length: 255)]
	protected string $domain;

	/**
	 * @var string  $secret
	 */
	#[DBField(name: 'secret', type: FieldTypes::TEXT, length: 255)]
	protected string $secret;

}