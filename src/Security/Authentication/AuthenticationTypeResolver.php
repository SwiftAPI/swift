<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication;

use Swift\Kernel\Attributes\Autowire;
use Swift\Security\Authentication\Token\AuthenticatedToken;
use Swift\Security\Authentication\Token\PreAuthenticatedToken;
use Swift\Security\Security;
use Swift\Security\User\AnonymousUser;
use Swift\Security\User\NullUser;

/**
 * Class AuthenticationTypeResolver
 * @package Swift\Security\Authentication
 */
#[Autowire]
class AuthenticationTypeResolver implements AuthenticationTypeResolverInterface {

    /**
     * AuthenticationTypeResolver constructor.
     *
     * @param Security $security
     */
    public function __construct(
        private Security $security,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function isPreAuthenticated(): bool {
        return $this->security->getToken() instanceof PreAuthenticatedToken;
    }

    /**
     * @inheritDoc
     */
    public function isAnonymous(): bool {
        return $this->security->getUser() instanceof AnonymousUser;
    }

    /**
     * @inheritDoc
     */
    public function isDirectLogin(): bool {
        return $this->security->getToken() instanceof AuthenticatedToken;
    }

    /**
     * @inheritDoc
     */
    public function isAuthenticated(): bool {
        return !$this->security->getUser() instanceof NullUser;
    }


}