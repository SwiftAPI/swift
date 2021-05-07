<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization;


use Swift\Kernel\TypeSystem\Enum;

/**
 * Class AuthorizationTypesEnum
 * @package Swift\Security\Authorization
 */
class AuthorizationTypesEnum extends Enum {

    public const IS_AUTHENTICATED = 'IS_AUTHENTICATED';
    public const IS_AUTHENTICATED_DIRECTLY = 'IS_AUTHENTICATED_DIRECTLY';
    public const IS_AUTHENTICATED_ANONYMOUSLY = 'IS_AUTHENTICATED_ANONYMOUSLY';
    public const IS_AUTHENTICATED_TOKEN = 'IS_AUTHENTICATED_TOKEN';
    public const PUBLIC_ACCESS = 'PUBLIC_ACCESS';

}