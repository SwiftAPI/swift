<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User;

use Swift\Security\Authorization\AuthorizationRolesEnum;

/**
 * Class AnonymousUser
 * @package Swift\Security\User
 */
class AnonymousUser implements UserInterface {

    protected UserRolesBag $roles;

    /**
     * AnonymousUser constructor.
     */
    public function __construct() {
        $this->roles = new UserRolesBag([AuthorizationRolesEnum::ROLE_GUEST => AuthorizationRolesEnum::ROLE_GUEST]);
    }


    /**
     * @inheritDoc
     */
    public function getCredential(): string {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getId(): ?int {
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