<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router\Model\Entity;

use stdClass;
use Swift\GraphQl\Attributes\Field as GraphQlField;
use Swift\GraphQl\Attributes\Type;
use Swift\GraphQl\Types\Type as GraphQlType;
use Swift\Model\Attributes\Table;
use Swift\Model\Entity;
use Swift\Model\Attributes\Field;
use Swift\Model\Types\FieldTypes;
use Swift\Model\Types\Serialize;

/**
 * Class LogRequest
 * @package Swift\Router\Model\Entity
 */
#[Table( name: 'log_request' ), Type]
class LogRequest extends Entity {

    /**
     * @var int $id
     */
    #[Field( name: 'id', primary: true, type: FieldTypes::INT, length: 11 )]
    protected int $id;

    /**
     * @var string $ip
     */
    #[Field( name: 'ip', type: FieldTypes::TEXT, length: 255 )]
    protected string $ip;

    /**
     * @var string $origin
     */
    #[Field( name: 'origin', type: FieldTypes::TEXT, length: 255 )]
    protected string $origin;

    /**
     * @var \DateTime $time
     */
    #[Field( name: 'time', type: FieldTypes::DATETIME )]
    protected \DateTime $time;

    /**
     * @var string $method
     */
    #[Field( name: 'method', type: FieldTypes::TEXT, length: 255 )]
    protected string $method;

    /**
     * @var stdClass $headers
     */
    #[Field( name: 'headers', type: FieldTypes::JSON )]
    protected stdClass $headers;

    /**
     * @var array $body
     */
    #[Field( name: 'body', type: FieldTypes::JSON ), GraphQlField(type: GraphQlType::STDCLASS)]
    protected array $body;

    /**
     * @var int $code
     */
    #[Field( name: 'code', type: FieldTypes::INT, length: 11 )]
    protected int $code;

}