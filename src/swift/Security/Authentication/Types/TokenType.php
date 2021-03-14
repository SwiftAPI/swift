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
 * Class TokenType
 * @package Swift\Security\Authentication\Types
 */
#[Type]
class TokenType {

    /**
     * TokenType constructor.
     *
     * @param string $token
     * @param \DateTime $expires
     */
    public function __construct(
        #[Field] public string $token,
        #[Field] public \DateTime $expires,
    ) {
    }

}