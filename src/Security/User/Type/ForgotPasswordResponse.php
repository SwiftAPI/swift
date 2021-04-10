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
use Swift\GraphQl\Attributes\Type;
use Swift\HttpFoundation\Response;

/**
 * Class ForgotPasswordResponse
 * @package Swift\Security\User\Type
 */
#[Type(description: 'Confirmation on success/failure to forgot password request')]
class ForgotPasswordResponse {

    /**
     * ForgotPasswordResponse constructor.
     *
     * @param string $message
     * @param int $code
     */
    public function __construct(
        #[Field] public string $message = 'Successfully requested reset password token. The user has been notified.',
        #[Field(description: 'HTTP response code')] public int $code = Response::HTTP_OK,
    ) {
    }

}