<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security;

use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\User\UserInterface;

/**
 * Class Security
 * @package Swift\Security
 */
#[Autowire]
class Security {

    private PassportInterface $passport;
    private UserInterface $user;
    private TokenInterface $token;

    /**
     * @return PassportInterface
     */
    public function getPassport(): PassportInterface {
        if (!isset($this->passport)) {
            throw new \RuntimeException('Authentication process has not finished yet');
        }

        return $this->passport;
    }

    /**
     * @param PassportInterface $passport
     */
    public function setPassport( PassportInterface $passport ): void {
        $this->passport = $passport;
    }

    /**
     * @return UserInterface
     */
    public function getUser(): UserInterface {
        if (!isset($this->user)) {
            throw new \RuntimeException('Authentication process has not finished yet');
        }
        return $this->user;
    }

    /**
     * @param UserInterface $user
     */
    public function setUser( UserInterface $user ): void {
        $this->user = $user;
    }

    /**
     * @return TokenInterface
     */
    public function getToken(): TokenInterface {
        if (!isset($this->token)) {
            throw new \RuntimeException('Authentication process has not finished yet');
        }

        return $this->token;
    }

    /**
     * @param TokenInterface $token
     */
    public function setToken( TokenInterface $token ): void {
        $this->token = $token;
    }





}