<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User;

use stdClass;
use Swift\Security\Authorization\AuthorizationRolesEnum;

/**
 * Class ClientUser
 * @package Swift\Security\User
 */
class ClientUser implements UserInterface {

    /**
     * User constructor.
     *
     * @param int $id
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUri
     * @param string|null $grantTypes
     * @param string|null $scope
     * @param \DateTime $created
     */
    public function __construct(
        private int $id,
        private string $clientId,
        private string $clientSecret,
        private string $redirectUri,
        private ?string $grantTypes,
        private ?string $scope,
        private \DateTime $created,
    ) {
        $this->roles = new UserRolesBag([AuthorizationRolesEnum::ROLE_CLIENT => AuthorizationRolesEnum::ROLE_CLIENT]);
    }

    private UserRolesBag $roles;

    /**
     * @inheritDoc
     */
    public function getCredential(): string {
        return $this->clientSecret;
    }

    /**
     * @inheritDoc
     */
    public function getId(): ?int {
        return $this->id;
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
        // TODO: Implement set() method.
    }


    public function serialize(): stdClass {
        $array = array();

        foreach (get_object_vars($this) as $prop => $value) {
            if ((is_object($value) && !is_a($value, stdClass::class) && !is_a($value, \DateTime::class))) {
                continue;
            }
            $array[$prop] = $value;
        }

        return (object) $array;
    }

}