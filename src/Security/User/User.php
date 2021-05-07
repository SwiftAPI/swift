<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User;

use stdClass;
use Swift\Security\Authentication\Passport\Credentials\PasswordCredentialsEncoder;
use Swift\Security\Authorization\AuthorizationRolesEnum;

/**
 * Class User
 * @package Swift\Security\User
 */
class User implements UserInterface {

    /**
     * User constructor.
     *
     * @param int $id
     * @param string $username
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @param string $password
     * @param \DateTime $created
     * @param \DateTime $modified
     */
    public function __construct(
        private int $id,
        private string $username,
        private string $email,
        private string $firstname,
        private string $lastname,
        private string $password,
        private \DateTime $created,
        private \DateTime $modified,
    ) {
        $this->roles = new UserRolesBag([AuthorizationRolesEnum::ROLE_USER => AuthorizationRolesEnum::ROLE_USER]);
    }

    private UserRolesBag $roles;
    private UserStorageInterface $userStorage;

    /**
     * @inheritDoc
     */
    public function getCredential(): string {
        return $this->password;
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
        $name .= ($name ?? ' ') . ($this->lastname ?? '');

        return $name ?? null;
    }

    public function getCreated(): \DateTime {
        if (!isset($this->created)) {
            $this->created = new \DateTime();
        }

        return $this->created;
    }

    public function getLastModified(): \DateTime {
        if (!isset($this->modified)) {
            $this->modified = new \DateTime();
        }

        return $this->modified;
    }

    public function getRoles(): UserRolesBag {
        return $this->roles;
    }

    public function set( array $state ): void {
        if (array_key_exists('password', $state)) {
            $state['password'] = (new PasswordCredentialsEncoder($state['password']))->getEncoded();
        }
        if (array_key_exists('modified', $state) && is_string($state['modified'])) {
            $state['modified'] = new \DateTime($state['modified']);
        }
        if (array_key_exists('created', $state)) {
            throw new \UnexpectedValueException('Cannot change created date of user');
        }

        $state['id'] = $this->getId();

        $this->userStorage->save($state);
    }

    public function serialize(): stdClass {
        $array = array();

        foreach (get_object_vars($this) as $prop => $value) {
            if ((is_object($value) && !is_a($value, stdClass::class) && !is_a($value, \DateTime::class)) || ($prop === 'password')) {
                continue;
            }
            $array[$prop] = $value;
        }

        return (object) $array;
    }

    public function setUserStorage( UserStorageInterface $userStorage ): void {
        $this->userStorage = $userStorage;
    }
}