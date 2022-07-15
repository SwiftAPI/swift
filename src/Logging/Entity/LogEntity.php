<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Logging\Entity;

use stdClass;
use Swift\DependencyInjection\Attributes\DI;
use Swift\Orm\Attributes\Behavior\CreatedAt;
use Swift\Orm\Entity\AbstractEntity;
use Swift\Orm\Attributes\Field;
use Swift\Orm\Attributes\Entity;
use Swift\Orm\Types\FieldTypes;

/**
 * Class Log
 * @package Swift\Logging\Entity\Log
 */
#[Entity( table: 'log' )]
#[CreatedAt( field: 'datetime' )]
#[DI( autowire: false )]
class LogEntity extends AbstractEntity {
    
    /**
     * @var int $id
     */
    #[Field( name: 'id', primary: true, type: FieldTypes::INT, length: 11 )]
    protected int $id;
    
    /**
     * @var string $channel
     */
    #[Field( name: 'channel', type: FieldTypes::TEXT, length: 255 )]
    protected string $channel;
    
    /**
     * @var string $message
     */
    #[Field( name: 'message', type: FieldTypes::TEXT )]
    protected string $message;
    
    /**
     * @var int $level
     */
    #[Field( name: 'level', type: FieldTypes::INT, length: 11 )]
    protected int $level;
    
    /**
     * @var string $levelName
     */
    #[Field( name: 'level_name', type: FieldTypes::TEXT, length: 255 )]
    protected string $levelName;
    
    /**
     * @var stdClass $context
     */
    #[Field( name: 'context', type: FieldTypes::JSON )]
    protected stdClass $context;
    
    /**
     * @var \DateTimeInterface $datetime
     */
    #[Field( name: 'datetime', type: FieldTypes::DATETIME )]
    protected \DateTimeInterface $datetime;
    
    
    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }
    
    /**
     * @return string
     */
    public function getChannel(): string {
        return $this->channel;
    }
    
    /**
     * @param string $channel
     */
    public function setChannel( string $channel ): void {
        $this->channel = $channel;
    }
    
    /**
     * @return string
     */
    public function getMessage(): string {
        return $this->message;
    }
    
    /**
     * @param string $message
     */
    public function setMessage( string $message ): void {
        $this->message = $message;
    }
    
    /**
     * @return int
     */
    public function getLevel(): int {
        return $this->level;
    }
    
    /**
     * @param int $level
     */
    public function setLevel( int $level ): void {
        $this->level = $level;
    }
    
    /**
     * @return string
     */
    public function getLevelName(): string {
        return $this->levelName;
    }
    
    /**
     * @param string $levelName
     */
    public function setLevelName( string $levelName ): void {
        $this->levelName = $levelName;
    }
    
    /**
     * @return \stdClass
     */
    public function getContext(): stdClass {
        return $this->context;
    }
    
    /**
     * @param \stdClass $context
     */
    public function setContext( stdClass $context ): void {
        $this->context = $context;
    }
    
    /**
     * @return \DateTimeInterface
     */
    public function getDatetime(): \DateTimeInterface {
        return $this->datetime;
    }
    
}