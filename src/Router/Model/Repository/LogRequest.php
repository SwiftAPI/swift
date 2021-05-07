<?php declare( strict_types=1 );
/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router\Model\Repository;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use stdClass;
use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\Type;
use Swift\GraphQl\Types\Type as GraphQlType;
use Swift\Model\Attributes\DBField;
use Swift\Model\Types\FieldTypes;

/**
 * Class LogRequest
 * @package Swift\Router\Model\Entity
 */
#[Entity, Table( name: 'log_request' ), Type]
class LogRequest {

    /**
     * @var int $id
     */
    #[Id, GeneratedValue, Column( name: 'id', type: 'integer', length: 11 )]
    protected int $id;

    /**
     * @var string $ip
     */
    #[Column( name: 'ip', type: 'string', length: 255 )]
    protected string $ip;

    /**
     * @var string $origin
     */
    #[Column( name: 'origin', type: 'string', length: 255 )]
    protected string $origin;

    /**
     * @var \DateTimeInterface $time
     */
    #[Column( name: 'time', type: 'datetime' )]
    protected \DateTimeInterface $time;

    /**
     * @var string $method
     */
    #[Column( name: 'method', type: 'string', length: 255 )]
    protected string $method;

    /**
     * @var array $headers
     */
    #[Column( name: 'headers', type: 'json_array' )]
    protected array $headers;

    /**
     * @var array $body
     */
    #[Column( name: 'body', type: 'json_array'), Field(type: GraphQlType::STDCLASS)]
    protected array $body;

    /**
     * @var int $code
     */
    #[Column( name: 'code', type: 'integer', length: 11 )]
    protected int $code;

    #[Field]
    public function test( string $lorem ): string {
        return $lorem;
    }

}