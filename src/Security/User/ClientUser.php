<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User;

use Ramsey\Uuid\UuidInterface;
use stdClass;
use Swift\Orm\EntityManager;
use Swift\Security\Authorization\AuthorizationRole;
use Swift\Security\User\Entity\OauthClientsEntity;

/**
 * Class ClientUser
 * @package Swift\Security\User
 */
class ClientUser implements UserInterface {
    
    public EntityManager $entityManager;
    public OauthClientsEntity $clientsEntity;
    protected UserRolesBag $roles;
    
    /**
     * User constructor.
     *
     * @param int                        $id
     * @param \Ramsey\Uuid\UuidInterface $uuid
     * @param string                     $clientId
     * @param string                     $clientSecret
     * @param string|null                $redirectUri
     * @param string|null                $grantTypes
     * @param string|null                $scope
     * @param \DateTime                  $created
     * @param \DateTimeInterface         $modified
     */
    public function __construct(
        protected int                $id,
        protected UuidInterface      $uuid,
        protected string             $clientId,
        protected string             $clientSecret,
        protected ?string            $redirectUri,
        protected ?string            $grantTypes,
        protected ?string            $scope,
        protected \DateTimeInterface $created,
        protected \DateTimeInterface $modified,
    ) {
        $this->roles = new UserRolesBag( [ AuthorizationRole::ROLE_CLIENT->value => AuthorizationRole::ROLE_CLIENT ] );
    }
    
    
    /**
     * @inheritDoc
     */
    public function getCredential(): UserCredentials {
        return new UserCredentials( null, null, $this->clientSecret, $this->created, $this->modified );
    }
    
    /**
     * @inheritDoc
     */
    public function getId(): ?int {
        return $this->id;
    }
    
    public function getUuid(): ?string {
        return $this->uuid->toString();
    }
    
    /**
     * @inheritDoc
     */
    public function getUsername(): string {
        return $this->clientId;
    }
    
    /**
     * @inheritDoc
     */
    public function getEmail(): ?string {
        return null;
    }
    
    /**
     * @inheritDoc
     */
    public function getFirstname(): ?string {
        return null;
    }
    
    /**
     * @inheritDoc
     */
    public function getLastname(): ?string {
        return null;
    }
    
    /**
     * @inheritDoc
     */
    public function getFullName(): ?string {
        return null;
    }
    
    public function getCreated(): \DateTime {
        return $this->created;
    }
    
    public function getLastModified(): \DateTime {
        return new \DateTime();
    }
    
    public function getRoles(): UserRolesBag {
        return $this->roles;
    }
    
    public function set( array $state ): void {
        if ( array_key_exists( 'modified', $state ) && is_string( $state[ 'modified' ] ) ) {
            $state[ 'modified' ] = new \DateTimeImmutable( $state[ 'modified' ] );
        }
        if ( array_key_exists( 'created', $state ) ) {
            throw new \UnexpectedValueException( 'Cannot change created date of user' );
        }
        
        $state[ 'id' ] = $this->getId();
        
        foreach ( $state as $key => $item ) {
            $this->clientsEntity->{$key} = $item;
        }
        
        $this->clientsEntity->persist( $this->clientsEntity );
        $this->clientsEntity->run();
    }
    
    
    public function serialize(): stdClass {
        $array = [];
        
        foreach ( get_object_vars( $this ) as $prop => $value ) {
            if ( ( is_object( $value ) && ! is_a( $value, stdClass::class ) && ! is_a( $value, \DateTime::class ) ) ) {
                continue;
            }
            $array[ $prop ] = $value;
        }
        
        return (object) $array;
    }
    
    /**
     * @param \Swift\Security\User\Entity\OauthClientsEntity $clientsEntity
     *
     * @return \Swift\Security\User\ClientUser
     */
    public function setClientsEntity( OauthClientsEntity $clientsEntity ): self {
        $this->clientsEntity = $clientsEntity;
        
        return $this;
    }
    
    /**
     * @param \Swift\Orm\EntityManager $entityManager
     *
     * @return \Swift\Security\User\ClientUser
     */
    public function setEntityManager( EntityManager $entityManager ): self {
        $this->entityManager = $entityManager;
        
        return $this;
    }
    
    public static function fromClientEntity( OauthClientsEntity $entity, EntityManager $entityManager ): self {
        return ( new self(
            $entity->getId(),
            $entity->getUuid(),
            $entity->getClientId(),
            $entity->getClientSecret(),
            $entity->getRedirectUri(),
            $entity->getGrantTypes(),
            $entity->getScope(),
            $entity->getCreated(),
            $entity->getModified(),
        ) )->setClientsEntity( $entity )->setEntityManager( $entityManager );
    }
    
    public function __debugInfo(): array {
        $info = (array) $this;
        
        unset( $info[ 'entityManager' ], $info[ 'clientsEntity' ] );
        
        return $info;
    }
    
}