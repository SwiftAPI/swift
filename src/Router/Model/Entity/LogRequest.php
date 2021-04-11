<?php declare( strict_types=1 );

namespace Swift\Router\Model\Entity;

use stdClass;
use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\Type;
use Swift\GraphQl\Types\Type as GraphQlType;
use Swift\Model\Attributes\DBTable;
use Swift\Model\Entity;
use Swift\Model\Attributes\DBField;
use Swift\Model\Types\FieldTypes;

/**
 * Class LogRequest
 * @package Swift\Router\Model\Entity
 */
#[DBTable( name: 'log_request' ), Type]
class LogRequest extends Entity {

    /**
     * @var int $id
     */
    #[DBField( name: 'id', primary: true, type: FieldTypes::INT, length: 11 )]
    protected int $id;

    /**
     * @var string $ip
     */
    #[DBField( name: 'ip', type: FieldTypes::TEXT, length: 255 )]
    protected string $ip;

    /**
     * @var string $origin
     */
    #[DBField( name: 'origin', type: FieldTypes::TEXT, length: 255 )]
    protected string $origin;

    /**
     * @var string $time
     */
    #[DBField( name: 'time', type: FieldTypes::DATETIME, serialize: [ 'datetime' ] )]
    protected string $time;

    /**
     * @var string $method
     */
    #[DBField( name: 'method', type: FieldTypes::TEXT, length: 255 )]
    protected string $method;

    /**
     * @var stdClass $headers
     */
    #[DBField( name: 'headers', type: FieldTypes::TEXT, serialize: [ 'json' ] )]
    protected stdClass $headers;

    /**
     * @var array $body
     */
    #[DBField( name: 'body', type: FieldTypes::JSON, serialize: [ 'json' ] ), Field(type: GraphQlType::STDCLASS)]
    protected array $body;

    /**
     * @var int $code
     */
    #[DBField( name: 'code', type: FieldTypes::INT, length: 11 )]
    protected int $code;

    #[Field]
    public function test( string $lorem ): string {
        return $lorem;
    }

}