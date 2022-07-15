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
use Swift\Security\Authentication\Passport\Credentials\PasswordCredentialsEncoder;
use Swift\Security\Authorization\AuthorizationRole;
use Swift\Security\User\Entity\UserEntity;

/**
 * Class User
 * @package Swift\Security\User
 */
class User implements UserInterface {
    
    private UserRolesBag $roles;
    private EntityManager $userStorage;
    private UserEntity $userEntity;
    
    /**
     * @param int                                          $id
     * @param \Ramsey\Uuid\UuidInterface                   $uuid
     * @param string                                       $username
     * @param string                                       $email
     * @param string                                       $firstname
     * @param string                                       $lastname
     * @param \Swift\Security\User\UserCredentialInterface $credential
     * @param \DateTime                                    $created
     * @param \DateTime                                    $modified
     */
    public function __construct(
        protected readonly int                $id,
        protected readonly UuidInterface      $uuid,
        protected string                      $username,
        protected string                      $email,
        protected string                      $firstname,
        protected string                      $lastname,
        protected UserCredentialInterface     $credential,
        protected readonly \DateTimeInterface $created,
        protected \DateTimeInterface          $modified,
    ) {
        $this->roles = new UserRolesBag( [ AuthorizationRole::ROLE_USER->value => AuthorizationRole::ROLE_USER ] );
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
    public function getCredential(): UserCredentialInterface {
        return $this->credential;
    }
    
    /**
     * @inheritDoc
     */
    public function getUsername(): string {
        return $this->username;
    }
    
    /**
     * @inheritDoc
     */
    public function getEmail(): ?string {
        return $this->email;
    }
    
    /**
     * @inheritDoc
     */
    public function getFirstname(): ?string {
        return $this->firstname;
    }
    
    /**
     * @inheritDoc
     */
    public function getLastname(): ?string {
        return $this->lastname;
    }
    
    /**
     * @inheritDoc
     */
    public function getFullName(): ?string {
        $name = $this->firstname ?? '';
        $name .= ( $name ? ' ' : '' ) . ( $this->lastname ?? '' );
        
        return $name ?? null;
    }
    
    public function getCreated(): \DateTimeInterface {
        return $this->created;
    }
    
    public function getLastModified(): \DateTimeInterface {
        return $this->modified;
    }
    
    public function getRoles(): UserRolesBag {
        return $this->roles;
    }
    
    public function set( array $state ): void {
        if ( array_key_exists( 'password', $state ) ) {
            $credentials = $this->userEntity->getCredentials();
            $credentials->setCredential( ( new PasswordCredentialsEncoder( $state[ 'password' ] ) )->getEncoded() );
        }
        if ( array_key_exists( 'modified', $state ) && is_string( $state[ 'modified' ] ) ) {
            $state[ 'modified' ] = new \DateTimeImmutable( $state[ 'modified' ] );
        }
        if ( array_key_exists( 'created', $state ) ) {
            throw new \UnexpectedValueException( 'Cannot change created date of user' );
        }
        
        $state[ 'id' ] = $this->getId();
        
        foreach ( $state as $key => $item ) {
            $this->userEntity->{$key} = $item;
        }
        
        $this->userStorage->persist( $this->userEntity );
        $this->userStorage->run();
    }
    
    public function serialize(): stdClass {
        return (object) [
            'id'        => $this->id,
            'uuid'      => $this->uuid->toString(),
            'username'  => $this->username,
            'email'     => $this->email,
            'firstname' => $this->firstname,
            'lastname'  => $this->lastname,
            'created'   => $this->created->format( \DateTimeInterface::ATOM ),
            'modified'  => $this->modified->format( \DateTimeInterface::ATOM ),
        ];
    }
    
    public function setUserStorage( EntityManager $userStorage ): void {
        $this->userStorage = $userStorage;
    }
    
    public function setUserEntity( UserEntity $userEntity ): self {
        $this->userEntity = $userEntity;
        
        return $this;
    }
    
    public function __debugInfo(): array {
        return [
            'id'         => $this->id,
            'uuid'       => $this->uuid,
            'username'   => $this->username,
            'email'      => $this->email,
            'firstname'  => $this->firstname,
            'lastname'   => $this->lastname,
            'credential' => $this->credential,
            'created'    => $this->created,
            'modified'   => $this->modified,
        ];
        
    }
    
    public static function fromUserEntity( UserEntity $entity ): self {
        return ( new self(
            $entity->getId(),
            $entity->getUuid(),
            $entity->getUsername(),
            $entity->getEmail(),
            $entity->getFirstname(),
            $entity->getLastname(),
            UserCredentials::fromUserCredentials( $entity->getCredentials() ),
            $entity->getCreated(),
            $entity->getModified(),
        ) )->setUserEntity( $entity );
    }
    
}