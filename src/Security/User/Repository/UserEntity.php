<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\Repository;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;

/**
 * Class UserEntity
 * @package Swift\Security\User\Repository
 */
#[Entity, Table(name: 'security_users'), Index(columns: ['password'])]
class UserEntity {

    #[Id, GeneratedValue, Column(name: 'id', type: 'integer', length: 11)]
    protected int $id;

    #[Column(name: 'username', type: 'string', length: 128, unique: true)]
    protected string $username;

    #[Column(name: 'first_name', type: 'string', length: 255)]
    protected string $firstname;

    #[Column(name: 'last_name', type: 'string', length: 255)]
    protected string $lastname;

    #[Column(name: 'email', type: 'string', length: 255, unique: true)]
    protected string $email;

    #[Column(name: 'password', type: 'string', length: 255)]
    protected string $password;

    #[Column(name: 'created', type: 'datetime')]
    protected \DateTimeInterface $created;

    #[Column(name: 'modified', type: 'datetime')]
    protected \DateTimeInterface $modified;

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
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
     * @return string
     */
    public function getPassword(): string {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword( string $password ): void {
        $this->password = $password;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreated(): \DateTimeInterface {
        return $this->created;
    }

    /**
     * @param \DateTimeInterface $created
     */
    public function setCreated( \DateTimeInterface $created ): void {
        $this->created = $created;
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