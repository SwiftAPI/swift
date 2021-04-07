<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Types;

use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\Type;

/**
 * Class AccessTokenRequestResponse
 * @package Swift\Security\Authentication\Type
 */
#[Type(description: 'Access token to for authenticated session')]
class AccessTokenRequestResponse extends TokenRequestResponse {

    /**
     * AuthTokenRequestResponse constructor.
     *
     * @param string $accessToken
     * @param \DateTimeInterface $expires
     * @param string $tokenType
     * @param string $refreshToken
     */
    public function __construct(
        #[Field(description: 'Access token value')] public string $accessToken,
        #[Field(type: \DateTime::class, description: 'Expiry for token')] public \DateTimeInterface $expires,
        #[Field(description: 'Type of access token')] public string $tokenType,
        #[Field(description: 'Value for refresh token. Refresh token can be used to retrieve a new access token without re-authentication')] public string $refreshToken,
    ) {
        parent::__construct($this->accessToken, $this->expires, $this->tokenType);
    }

}