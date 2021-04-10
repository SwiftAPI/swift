<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Token;

use Swift\Kernel\Attributes\DI;
use Swift\Security\User\UserInterface;

/**
 * Class ResetPasswordToken
 * @package Swift\Security\Authentication\Token
 */
#[DI(autowire: false)]
class ResetPasswordToken extends AbstractToken {

    /**
     * ResetPasswordToken constructor.
     *
     * @param UserInterface $user
     * @param string $scope
     * @param string|null $token
     * @param bool $isAuthenticated
     */
    public function __construct(
        protected UserInterface $user,
        protected string $scope = TokenInterface::SCOPE_REFRESH_TOKEN,
        protected ?string $token = null,
        protected bool $isAuthenticated = false,
    ) {
        $this->expires = (new \DateTime())->modify('+ 30 minutes');
        parent::__construct($this->user, $this->scope, $this->token, $this->isAuthenticated);
    }

}