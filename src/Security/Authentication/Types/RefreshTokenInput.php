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
use Swift\GraphQl\Attributes\InputType;

/**
 * Class RefreshTokenInput
 * @package Swift\Security\Authentication\Types
 */
#[InputType]
class RefreshTokenInput {

    /**
     * RefreshTokenInput constructor.
     *
     * @param string $refreshToken
     * @param string $grantType
     */
    public function __construct(
        #[Field] public string $refreshToken,
        #[Field] public string $grantType,
    ) {
    }

}