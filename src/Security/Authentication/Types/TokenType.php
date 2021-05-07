<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Types;

use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\Type;

/**
 * Class TokenType
 * @package Swift\Security\Authentication\Types
 */
#[Type(description: 'Token to represent authenticated session')]
class TokenType {

    /**
     * TokenType constructor.
     *
     * @param string $token
     * @param \DateTime $expires
     */
    public function __construct(
        #[Field(description: 'The token value')] public string $token,
        #[Field(description: 'Moment the token expires')] public \DateTime $expires,
    ) {
    }

}