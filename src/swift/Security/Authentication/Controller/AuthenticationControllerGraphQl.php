<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Controller;


use Swift\Controller\AbstractController;
use Swift\GraphQl\Attributes\Mutation;
use Swift\Security\Authentication\Types\ClientCredentialsInput;
use Swift\Security\Authentication\Types\TokenRequestResponse;

/**
 * Class AuthenticationControllerGraphQl
 * @package Swift\Security\Authentication\Controller
 */
class AuthenticationControllerGraphQl extends AbstractController {

    /**
     * @param ClientCredentialsInput $credentials
     *
     * @return TokenRequestResponse
     */
    #[Mutation(name: 'authTokenGet')]
    public function token(ClientCredentialsInput $credentials): TokenRequestResponse {
        return new TokenRequestResponse(
            $this->getSecurityToken()->getTokenString(),
            $this->getSecurityToken()->expiresAt(),
            'bearer',
            $this->getSecurityToken()->getRefreshToken()->getTokenString(),
        );
    }

}