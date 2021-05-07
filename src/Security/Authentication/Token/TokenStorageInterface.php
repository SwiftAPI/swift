<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Token;

use Swift\Kernel\Attributes\DI;
use Swift\Security\Authentication\DiTags;

/**
 * Interface TokenStorageInterface
 * @package Swift\Security\Authentication\Token
 */
#[DI(tags: [DiTags::SECURITY_TOKEN_STORAGE])]
interface TokenStorageInterface {

    /**
     * Determine whether storage supports token
     *
     * @param TokenInterface $token
     *
     * @return bool
     */
    public function supports( TokenInterface $token ): bool;

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