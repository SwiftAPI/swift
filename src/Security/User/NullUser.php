<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User;

use Swift\Security\Authorization\AuthorizationRole;

/**
 * Class NullUser
 * @package Swift\Security\User
 */
class NullUser implements UserInterface {
    
    protected UserRolesBag $roles;
    
    /**
     * AnonymousUser constructor.
     */
    public function __construct() {
        $this->roles = new UserRolesBag( [ AuthorizationRole::ROLE_GUEST ] );
    }
    
    /**
     * @inheritDoc
     */
    public function getCredential(): UserCredentialInterface {
        return new UserCredentials( null, null, null, null, null );
    }
    
    /**
     * @inheritDoc
     */
    public function getId(): ?int {
        return null;
    }
    
    public function getUuid(): ?string {
        return null;
    }
    
    /**
     * @inheritDoc
     */
    public function getUsername(): string {
        return '';
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
        return new \DateTime();
    }
    
    public function getLastModified(): \DateTime {
        return new \DateTime();
    }
    
    public function getRoles(): UserRolesBag {
        return $this->roles;
    }
    
    public function set( array $state ): void {
        // TODO: Implement set() method.
    }
    
    
}