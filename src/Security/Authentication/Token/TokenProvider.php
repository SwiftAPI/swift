<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Token;


use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\User\UserInterface;

/**
 * Class TokenProvider
 * @package Swift\Security\Authentication\Token
 */
class TokenProvider implements TokenProviderInterface {



    /**
     * @inheritDoc
     */
    public function getTokenByUser( UserInterface|PassportInterface $user ): TokenInterface {
        // TODO: Implement getTokenByUser() method.
    }
}