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
use Swift\Orm\Entity\AbstractEntity;
use Swift\Orm\Attributes\Entity;
use Swift\Orm\Attributes\Field;
use Swift\Orm\Types\FieldTypes;

/**
 * Class LogRequest
 * @package Swift\Router\Model\Entity
 */
#[Entity( table: 'log_request' ), Type]
class LogRequest extends AbstractEntity {

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
     * @var \DateTimeInterface $time
     */
    #[Field( name: 'time', type: FieldTypes::DATETIME )]
    protected \DateTimeInterface $time;

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
     * @var stdClass|null $body
     */
    #[Field( name: 'body', type: FieldTypes::JSON ), GraphQlField(type: GraphQlType::STDCLASS)]
    protected ?stdClass $body;

    /**
     * @var int $code
     */
    #[Field( name: 'code', type: FieldTypes::INT, length: 11 )]
    protected int $code;
    
    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }
    
    /**
     * @return string
     */
    public function getIp(): string {
        return $this->ip;
    }
    
    /**
     * @param string $ip
     */
    public function setIp( string $ip ): void {
        $this->ip = $ip;
    }
    
    /**
     * @return string
     */
    public function getOrigin(): string {
        return $this->origin;
    }
    
    /**
     * @param string $origin
     */
    public function setOrigin( string $origin ): void {
        $this->origin = $origin;
    }
    
    /**
     * @return \DateTimeInterface
     */
    public function getTime(): \DateTimeInterface {
        return $this->time;
    }
    
    /**
     * @param \DateTimeInterface $time
     */
    public function setTime( \DateTimeInterface $time ): void {
        $this->time = $time;
    }
    
    /**
     * @return string
     */
    public function getMethod(): string {
        return $this->method;
    }
    
    /**
     * @param string $method
     */
    public function setMethod( string $method ): void {
        $this->method = $method;
    }
    
    /**
     * @return \stdClass
     */
    public function getHeaders(): stdClass {
        return $this->headers;
    }
    
    /**
     * @param \stdClass $headers
     */
    public function setHeaders( stdClass $headers ): void {
        $this->headers = $headers;
    }
    
    /**
     * @return \stdClass|null
     */
    public function getBody(): ?stdClass {
        return $this->body;
    }
    
    /**
     * @param \stdClass|null $body
     */
    public function setBody( ?stdClass $body ): void {
        $this->body = $body;
    }
    
    /**
     * @return int
     */
    public function getCode(): int {
        return $this->code;
    }
    
    /**
     * @param int $code
     */
    public function setCode( int $code ): void {
        $this->code = $code;
    }
    
    

}