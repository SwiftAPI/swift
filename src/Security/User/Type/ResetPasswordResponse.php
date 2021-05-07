<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\Type;

use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\Type;
use Swift\HttpFoundation\Response;

/**
 * Class ResetPasswordResponse
 * @package Swift\Security\User\Type
 */
#[Type(description: 'After reset password response')]
class ResetPasswordResponse {

    /**
     * ForgotPasswordResponse constructor.
     *
     * @param string $message
     * @param int $code
     */
    public function __construct(
        #[Field(description: 'Status message')] public string $message = 'Successfully reset password.',
        #[Field(description: 'HTTP status code')] public int $code = Response::HTTP_OK,
    ) {
    }

}