<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Token;

/**
 * Interface TokenStoragePoolInterface
 * @package Swift\Security\Authentication\Token
 */
interface TokenStoragePoolInterface {

    /**
     * Returns the current security token
     *
     * @param string $accessToken
     *
     * @return TokenInterface|null
     */
    public function getToken(string $accessToken): ?TokenInterface;

    /**
     * Set the security token
     *
     * @param TokenInterface|null $token
     */
    public function setToken( ?TokenInterface $token = null ): void;


}