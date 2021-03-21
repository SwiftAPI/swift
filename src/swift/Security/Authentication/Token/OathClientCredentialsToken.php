<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Token;

use Swift\Security\User\UserInterface;

/**
 * Class OathClientCredentialsToken
 * @package Swift\Security\Authentication\Token
 */
class OathClientCredentialsToken extends AbstractToken {

    /**
     * Token constructor.
     *
     * @param UserInterface $user
     * @param string|null $token
     * @param TokenInterface|null $refreshToken
     * @param bool $isAuthenticated
     */
    public function __construct(
        protected UserInterface $user,
        protected ?string $token = null,
        protected ?TokenInterface $refreshToken = null,
        protected bool $isAuthenticated = false,
    ) {
        $this->refreshToken = new OauthRefreshToken($this->user, TokenInterface::SCOPE_REFRESH_TOKEN);

        parent::__construct($user, TokenInterface::SCOPE_ACCESS_TOKEN, $token, $isAuthenticated);

        $this->userId = null;
        $this->clientId = $this->getUser()->getId();
    }

    public function getRefreshToken(): TokenInterface {
        return $this->refreshToken;
    }


}