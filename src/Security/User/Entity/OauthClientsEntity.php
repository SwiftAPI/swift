<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\Entity;

use Ramsey\Uuid\UuidInterface;
use Swift\DependencyInjection\Attributes\DI;
use Swift\Orm\Attributes\Behavior\CreatedAt;
use Swift\Orm\Attributes\Behavior\UpdatedAt;
use Swift\Orm\Attributes\Behavior\Uuid\Uuid1;
use Swift\Orm\Entity\AbstractEntity;
use Swift\Orm\Attributes\Entity;
use Swift\Orm\Attributes\Field;
use Swift\Orm\Entity\EntityInterface;
use Swift\Orm\Types\FieldTypes;

/**
 * Class OauthClientsEntity
 * @package Swift\Security\Authentication\Entity
 */
#[DI( aliases: [ EntityInterface::class . ' $oauthClientsEntity', EntityInterface::class . ' $securityClientsEntity' ] )]
#[Entity( table: 'security_clients' )]
#[Uuid1( field: 'uuid' )]
#[CreatedAt( field: 'created' )]
#[UpdatedAt( field: 'modified' )]
class OauthClientsEntity extends AbstractEntity {
    
    #[Field( name: 'id', primary: true, type: FieldTypes::INT, length: 11 )]
    public int $id;
    
    #[Field( name: 'uuid', type: FieldTypes::UUID )]
    protected UuidInterface $uuid;
    
    #[Field( name: 'client_id', type: FieldTypes::STRING, empty: false, unique: true )]
    public string $clientId;
    
    #[Field( name: 'client_secret', type: FieldTypes::STRING, length: 80, empty: true )]
    public ?string $clientSecret;
    
    #[Field( name: 'redirect_uri', type: FieldTypes::LONGTEXT, length: 2000, empty: true )]
    public ?string $redirectUri;
    
    #[Field( name: 'grant_types', type: FieldTypes::STRING, length: 80, empty: true )]
    public ?string $grantTypes;
    
    #[Field( name: 'scope', type: FieldTypes::LONGTEXT, length: 4000, empty: true )]
    public ?string $scope;
    
    #[Field( name: 'created', type: FieldTypes::DATETIME, empty: false )]
    public \DateTimeInterface $created;
    
    #[Field( name: 'modified', type: FieldTypes::DATETIME, empty: false )]
    public \DateTimeInterface $modified;
    
    /**
     * @return int
     */
    public function getId(): int {
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
    public function getClientId(): string {
        return $this->clientId;
    }
    
    /**
     * @param string $clientId
     */
    public function setClientId( string $clientId ): void {
        $this->clientId = $clientId;
    }
    
    /**
     * @return string|null
     */
    public function getClientSecret(): ?string {
        return $this->clientSecret;
    }
    
    /**
     * @param string|null $clientSecret
     */
    public function setClientSecret( ?string $clientSecret ): void {
        $this->clientSecret = $clientSecret;
    }
    
    /**
     * @return string|null
     */
    public function getRedirectUri(): ?string {
        return $this->redirectUri ?? null;
    }
    
    /**
     * @param string|null $redirectUri
     */
    public function setRedirectUri( ?string $redirectUri ): void {
        $this->redirectUri = $redirectUri;
    }
    
    /**
     * @return string|null
     */
    public function getGrantTypes(): ?string {
        return $this->grantTypes ?? null;
    }
    
    /**
     * @param string|null $grantTypes
     */
    public function setGrantTypes( ?string $grantTypes ): void {
        $this->grantTypes = $grantTypes;
    }
    
    /**
     * @return string|null
     */
    public function getScope(): ?string {
        return $this->scope ?? null;
    }
    
    /**
     * @param string|null $scope
     */
    public function setScope( ?string $scope ): void {
        $this->scope = $scope;
    }
    
    /**
     * @return \DateTimeInterface
     */
    public function getCreated(): \DateTimeInterface {
        return $this->created;
    }
    
    /**
     * @return \DateTimeInterface
     */
    public function getModified(): \DateTimeInterface {
        return $this->modified;
    }
    
    /**
     * @param \DateTimeInterface $modified
     */
    public function setModified( \DateTimeInterface $modified ): void {
        $this->modified = $modified;
    }
    
    
    
    
    
}