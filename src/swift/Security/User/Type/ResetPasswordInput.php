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
use Swift\Kernel\Attributes\DI;

/**
 * Class ResetPasswordInput
 * @package Swift\Security\User\Type
 */
#[DI(autowire: false), InputType(description: 'Reset password input')]
class ResetPasswordInput {

    /**
     * ResetPasswordInput constructor.
     *
     * @param string $resetPasswordToken
     * @param string $newPassword
     */
    public function __construct(
        #[Field(description: 'Token will available for the user through ForgotPassword')] private string $resetPasswordToken,
        #[Field(description: 'The new user password')] private string $newPassword,
    ) {
    }

}