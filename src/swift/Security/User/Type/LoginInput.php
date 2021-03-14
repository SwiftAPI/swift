<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\Type;

use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\InputType;
use Swift\GraphQl\Attributes\Type;
use Swift\Kernel\Attributes\DI;

#[DI(autowire: false), InputType]
class LoginInput {

    /**
     * LoginInputType constructor.
     *
     * @param string $username
     * @param string $password
     */
    public function __construct(
        #[Field] public string $username,
        #[Field] public string $password,
    ) {
    }
}