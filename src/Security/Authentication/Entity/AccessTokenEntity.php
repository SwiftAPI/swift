<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Entity;

use Ramsey\Uuid\UuidInterface;
use Swift\DependencyInjection\Attributes\DI;
use Swift\Orm\Attributes\Behavior\Uuid\Uuid1;
use Swift\Orm\Attributes\Relation\BelongsTo;
use Swift\Orm\Entity\AbstractEntity;
use Swift\Orm\Attributes\Entity;
use Swift\Orm\Attributes\Field;
use Swift\Orm\Entity\EntityInterface;
use Swift\Orm\Types\FieldTypes;
use Swift\Security\User\Entity\OauthClientsEntity;
use Swift\Security\User\Entity\UserEntity;

/**
 * Class AccessTokenEntity
 * @package Swift\Authorization\Model
 */
#[DI( aliases: [ EntityInterface::class . ' $accessTokenEntity' ] )]
#[Entity( table: 'security_access_tokens' )]
#[Uuid1( field: 'uuid' )]
class AccessTokenEntity extends AbstractEntity {
    
    #[Field( name: 'id', primary: true, type: FieldTypes::INT, length: 11 )]
    public int $id;
    
    #[Field( name: 'uuid', type: FieldTypes::UUID )]
    protected UuidInterface $uuid;
    
    #[Field( name: 'access_token', type: FieldTypes::TEXT, length: 40, empty: false )]
    public string $accessToken;
    
    #[Field( name: 'expires', type: FieldTypes::DATETIME, empty: false )]
    public \DateTimeInterface $expires;
    
    #[Field( name: 'scope', type: FieldTypes::LONGTEXT, length: 4000, empty: true )]
    public ?string $scope;
    
    #[BelongsTo( targetEntity: OauthClientsEntity::class, inverseAs: 'accessTokens', nullable: true  )]
    public ?OauthClientsEntity $client;
    
    #[BelongsTo( targetEntity: UserEntity::class, inverseAs: 'accessTokens', nullable: true  )]
    public ?UserEntity $user;
    
    /**
     * @return int|null
     */
    public function getId(): ?int {
        return $this->id;
    }
    
    /**
     * @return \Ramsey\Uuid\UuidInterface
     */
    public function getUuid(): UuidInterface {
        return $this->uuid;
    }
    
    /**
     * @return string
     */
    public function getAccessToken(): string {
        return $this->accessToken;
    }
    
    /**
     * @param string $accessToken
     */
    public function setAccessToken( string $accessToken ): void {
        $this->accessToken = $accessToken;
    }
    
    /**
     * @return \DateTimeInterface
     */
    public function getExpires(): \DateTimeInterface {
        return $this->expires;
    }
    
    /**
     * @param \DateTimeInterface $expires
     */
    public function setExpires( \DateTimeInterface $expires ): void {
        $this->expires = $expires;
    }
    
    /**
     * @return string|null
     */
    public function getScope(): ?string {
        return $this->scope;
    }
    
    /**
     * @param string|null $scope
     */
    public function setScope( ?string $scope ): void {
        $this->scope = $scope;
    }
    
    /**
     * @return \Swift\Security\User\Entity\OauthClientsEntity|null
     */
    public function getClient(): ?OauthClientsEntity {
        return $this->client ?? null;
    }
    
    /**
     * @param \Swift\Security\User\Entity\OauthClientsEntity|null $client
     */
    public function setClient( ?OauthClientsEntity $client ): void {
        $this->client = $client;
    }
    
    /**
     * @return \Swift\Security\User\Entity\UserEntity|null
     */
    public function getUser(): ?UserEntity {
        return $this->user;
    }
    
    /**
     * @param \Swift\Security\User\Entity\UserEntity|null $user
     */
    public function setUser( ?UserEntity $user ): void {
        $this->user = $user;
    }
    
    
    
}