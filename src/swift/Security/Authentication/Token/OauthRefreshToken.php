<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Token;

use DateTime;
use Swift\Security\User\UserInterface;

/**
 * Class OauthRefreshToken
 * @package Swift\Security\Authentication\Token
 */
class OauthRefreshToken extends AbstractToken {

    /**
     * AbstractToken constructor.
     *
     * @param UserInterface $user
     * @param string $scope
     * @param string|null $token
     * @param bool $isAuthenticated
     */
    public function __construct(
        protected UserInterface $user,
        protected string $scope,
        protected ?string $token = null,
        protected bool $isAuthenticated = false,
    ) {
        parent::__construct($user, $scope, $token, $isAuthenticated);

        $this->expires = (new DateTime())->modify('+ 1 month');
    }

}