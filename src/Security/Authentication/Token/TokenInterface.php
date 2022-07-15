<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Token;

use stdClass;
use Swift\Security\User\UserInterface;

/**
 * Interface TokenInterface
 * @package Swift\Security\Authentication\Token
 */
interface TokenInterface {

    public const SCOPE_ACCESS_TOKEN = 'SCOPE_ACCESS_TOKEN';
    public const SCOPE_REFRESH_TOKEN = 'SCOPE_REFRESH_TOKEN';
    public const SCOPE_RESET_PASSWORD = 'SCOPE_RESET_PASSWORD';
    
    public function getId(): ?int;

    /**
     * @return UserInterface
     */
    public function getUser(): UserInterface;

    /**
     * Check whether token has not expired yet
     *
     * @return bool
     */
    public function hasNotExpired(): bool;

    /**
     * Returns moment the token will expire
     *
     * @return \DateTimeInterface
     */
    public function expiresAt(): \DateTimeInterface;

    public function getTokenString(): string;

    /**
     * Check whether token is authenticated
     *
     * @return bool
     */
    public function isAuthenticated(): bool;

    /**
     * Set is authenticated
     *
     * @param bool $isAuthenticated
     */
    public function setIsAuthenticated( bool $isAuthenticated ): void;

    /**
     * Return all token data
     *
     * @return stdClass
     */
    public function getData(): stdClass;

    public function getScope(): string;

}