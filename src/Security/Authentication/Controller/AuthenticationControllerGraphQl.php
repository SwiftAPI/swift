<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Controller;


use Swift\Controller\AbstractController;
use Swift\GraphQl\Attributes\Mutation;
use Swift\GraphQl\Attributes\Query;
use Swift\Security\Authentication\Types\AccessTokenRequestResponse;
use Swift\Security\Authentication\Types\ClientCredentialsInput;
use Swift\Security\Authentication\Types\RefreshTokenInput;
use Swift\Security\Authentication\Types\TokenRequestResponse;
use Swift\Security\Authorization\AuthorizationTypesEnum;

/**
 * Class AuthenticationControllerGraphQl
 * @package Swift\Security\Authentication\Controller
 */
class AuthenticationControllerGraphQl extends AbstractController {

    /**
     * @param ClientCredentialsInput $credentials
     *
     * @return AccessTokenRequestResponse
     */
    #[Mutation( name: 'AuthAccessTokenGet', description: 'Oauth client credentials endpoint' )]
    public function token( ClientCredentialsInput $credentials ): AccessTokenRequestResponse {
        return new AccessTokenRequestResponse(
            $this->getSecurityToken()->getTokenString(),
            $this->getSecurityToken()->expiresAt(),
            'bearer',
            $this->getSecurityToken()->getRefreshToken()->getTokenString(),
        );
    }

    /**
     * @param RefreshTokenInput $refreshToken
     *
     * @return TokenRequestResponse
     */
    #[Mutation( name: 'AuthRefreshToken', description: 'Fetch new accessToken with refreshToken' )]
    public function refresh( RefreshTokenInput $refreshToken ): TokenRequestResponse {
        return new TokenRequestResponse(
            $this->getSecurityToken()->getTokenString(),
            $this->getSecurityToken()->expiresAt(),
            'bearer',
        );
    }

}