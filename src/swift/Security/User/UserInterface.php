<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
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
     * @return string
     */
    public function getCredential(): string;

    /**
     * @return int|null
     */
    public function getId(): ?int;

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

    public function getCreated(): \DateTime;

    public function getLastModified(): \DateTime;

    public function getRoles(): UserRolesBag;

    public function set( array $state ): void;

}