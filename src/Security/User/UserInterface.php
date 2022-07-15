<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User;

/**
 * Interface UserInterface
 * @package Swift\Security\User
 */
interface UserInterface {
    
    /**
     * Get credentials belonging to user
     *
     * @return \Swift\Security\User\UserCredentialInterface
     */
    public function getCredential(): UserCredentialInterface;
    
    /**
     * @return int|null
     */
    public function getId(): ?int;
    
    public function getUuid(): ?string;

    /**
     * @return string
     */
    public function getUsername(): string;

    /**
     * @return string|null
     */
    public function getEmail(): ?string;

    /**
     * @return string|null
     */
    public function getFirstname(): ?string;

    /**
     * @return string|null
     */
    public function getLastname(): ?string;

    /**
     * @return string|null
     */
    public function getFullName(): ?string;

    public function getCreated(): \DateTimeInterface;

    public function getLastModified(): \DateTimeInterface;

    public function getRoles(): UserRolesBag;

    public function set( array $state ): void;

}