<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\Type;

use DateTime;
use stdClass;
use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\Type;
use Swift\Kernel\Attributes\DI;
use Swift\Security\Authentication\Types\TokenType;

/**
 * Class LoginResponseType
 * @package Swift\Security\User\Type
 */
#[DI(autowire: false), Type]
class LoginResponseType {

    /**
     * LoginResponseType constructor.
     *
     * @param UserEdge $user
     * @param TokenType $token
     */
    public function __construct(
        #[Field(description: 'User object after authentication')] public UserEdge $user,
        #[Field(description: 'Authenticated token')] public TokenType $token,
    ) {
    }

}