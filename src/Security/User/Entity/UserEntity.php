<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\Entity;

use JetBrains\PhpStorm\ArrayShape;
use Ramsey\Uuid\UuidInterface;
use Swift\DependencyInjection\Attributes\DI;
use Swift\Orm\Attributes\Behavior\CreatedAt;
use Swift\Orm\Attributes\Behavior\UpdatedAt;
use Swift\Orm\Attributes\Behavior\Uuid\Uuid1;
use Swift\Orm\Attributes\Entity;
use Swift\Orm\Attributes\Field;
use Swift\Orm\Attributes\Index;
use Swift\Orm\Attributes\Relation\HasOne;
use Swift\Orm\Attributes\Relation\Inverse;
use Swift\Orm\Entity\EntityInterface;
use Swift\Orm\Mapping\Definition\Relation\EntityRelationType;
use Swift\Orm\Types\FieldTypes;
use Swift\Security\User\UserStorageInterface;
use Swift\Orm\Entity\AbstractEntity;

/**
 * Class UserEntity
 * @package Swift\Security\User\Entity
 */
#[DI( aliases: [ EntityInterface::class . ' $userEntity', UserStorageInterface::class . ' $userDatabaseStorage' ] )]
#[Entity( table: 'security_users' )]
#[CreatedAt( field: 'created' )]
#[UpdatedAt( field: 'modified' )]
#[Uuid1( field: 'uuid' )]
#[Index( fields: [ 'uuid', 'created' ], unique: true )]
class UserEntity extends AbstractEntity implements UserStorageInterface {
    
    #[Field( name: 'id', primary: true, type: FieldTypes::INT, length: 11 )]
    protected int $id;
    
    #[Field( name: 'uuid', type: FieldTypes::UUID, index: true )]
    protected UuidInterface $uuid;
    
    #[Field( name: 'username', type: FieldTypes::STRING, length: 128, unique: true)]
    protected string $username;
    
    #[Field( name: 'first_name', type: FieldTypes::STRING, length: 255, empty: true )]
    protected string $firstname;
    
    #[Field( name: 'last_name', type: FieldTypes::STRING, length: 255, empty: true )]
    protected string $lastname;
    
    #[Field( name: 'email', type: FieldTypes::STRING, length: 255, unique: true )]
    protected string $email;
    
    #[Field( name: 'created', type: FieldTypes::DATETIME )]
    protected \DateTimeInterface $created;
    
    #[Field( name: 'modified', type: FieldTypes::DATETIME )]
    protected \DateTimeInterface $modified;
    
    #[HasOne( targetEntity: UserCredentials::class, inverse: new Inverse( as: 'user', type: EntityRelationType::BELONGS_TO ), nullable: false )]
    protected UserCredentials $credentials;
    
    public function __construct() {
        $this->credentials = new UserCredentials();
    }
    
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
    public function getUsername(): string {
        return $this->username;
    }
    
    /**
     * @param string $username
     */
    public function setUsername( string $username ): void {
        $this->username = $username;
    }
    
    /**
     * @return string
     */
    public function getFirstname(): string {
        return $this->firstname;
    }
    
    /**
     * @param string $firstname
     */
    public function setFirstname( string $firstname ): void {
        $this->firstname = $firstname;
    }
    
    /**
     * @return string
     */
    public function getLastname(): string {
        return $this->lastname;
    }
    
    /**
     * @param string $lastname
     */
    public function setLastname( string $lastname ): void {
        $this->lastname = $lastname;
    }
    
    /**
     * @return string
     */
    public function getEmail(): string {
        return $this->email;
    }
    
    /**
     * @param string $email
     */
    public function setEmail( string $email ): void {
        $this->email = $email;
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
    
    /**
     * @return \Swift\Security\User\Entity\UserCredentials
     */
    public function getCredentials(): UserCredentials {
        return $this->credentials;
    }
    
    /**
     * @param \Swift\Security\User\Entity\UserCredentials $credentials
     */
    public function setCredentials( UserCredentials $credentials ): void {
        $this->credentials = $credentials;
    }
    
    #[ArrayShape( [ 'id' => "int", 'uuid' => "string", 'username' => "string", 'firstname' => "string", 'lastname' => "string", 'email' => "string", 'created' => "string", 'modified' => "string" ] )]
    public function getSimple(): array {
        return [
            'id' => $this->getId(),
            'uuid' => $this->getUuid()->toString(),
            'username' => $this->getUsername(),
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'email' => $this->getEmail(),
            'created' => $this->getCreated()->format( 'Y-m-d H:i:s' ),
            'modified' => $this->getModified()->format( 'Y-m-d H:i:s' ),
        ];
    }
    
}