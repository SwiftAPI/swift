<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\RateLimiter\Storage;


use Swift\DependencyInjection\Attributes\DI;
use Swift\Orm\Attributes\Behavior\CreatedAt;
use Swift\Orm\Attributes\Entity;
use Swift\Orm\Attributes\Field;
use Swift\Orm\Attributes\Index;
use Swift\Orm\Types\FieldTypes;

#[Entity( table: 'security_rate_limiter_tokens' )]
#[CreatedAt( field: 'createdAt' )]
#[Index( fields: [ 'rateName', 'stateId' ] )]
#[DI( autowire: false )]
class LimiterTokenEntity extends \Swift\Orm\Entity\AbstractEntity {
    
    #[Field( name: 'id', primary: true, type: FieldTypes::INT )]
    protected int $id;
    
    #[Field( name: 'rate_name', type: FieldTypes::STRING )]
    protected string $rateName;
    
    #[Field( name: 'state_id', type: FieldTypes::STRING )]
    protected string $stateId;
    
    #[Field( name: 'tokens', type: FieldTypes::INT )]
    protected int $tokens;
    
    #[Field( name: 'created_at', type: FieldTypes::DATETIME, index: true )]
    protected \DateTimeInterface $createdAt;
    
    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }
    
    /**
     * @return string
     */
    public function getRateName(): string {
        return $this->rateName;
    }
    
    /**
     * @param string $rateName
     *
     * @return LimiterTokenEntity
     */
    public function setRateName( string $rateName ): LimiterTokenEntity {
        $this->rateName = $rateName;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getStateId(): string {
        return $this->stateId;
    }
    
    /**
     * @param string $stateId
     *
     * @return LimiterTokenEntity
     */
    public function setStateId( string $stateId ): LimiterTokenEntity {
        $this->stateId = $stateId;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getTokens(): int {
        return $this->tokens;
    }
    
    /**
     * @param int $tokens
     *
     * @return LimiterTokenEntity
     */
    public function setTokens( int $tokens ): LimiterTokenEntity {
        $this->tokens = $tokens;
        
        return $this;
    }
    
    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface {
        return $this->createdAt;
    }
    
}