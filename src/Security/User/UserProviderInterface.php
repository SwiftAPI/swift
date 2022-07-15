<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User;


use Swift\DependencyInjection\Attributes\DI;

/**
 * Interface UserProviderInterface
 * @package Swift\Security\Authentication
 */
#[DI(tags: [DiTags::SECURITY_USER_PROVIDER])]
interface UserProviderInterface {

    /**
     * Fetch user by username
     *
     * @param string $username
     *
     * @return UserInterface|null
     */
    public function getUserByUsername(string $username): ?UserInterface;

    /**
     * Fetch user by email
     *
     * @param string $email
     *
     * @return UserInterface|null
     */
    public function getUserByEmail( string $email ): ?UserInterface;

    /**
     * Fetch user by id
     *
     * @param int $id
     *
     * @return UserInterface|null
     */
    public function getUserById( int $id ): ?UserInterface;

    /**
     * Store new user
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     *
     * @return UserInterface
     */
    public function storeUser( string $username, string $password, string $email, string $firstname, string $lastname ): UserInterface;

}