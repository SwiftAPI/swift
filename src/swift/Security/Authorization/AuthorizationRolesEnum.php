<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization;


use Swift\Kernel\TypeSystem\Enum;

/**
 * Class AuthorizationRolesEnum
 * @package Swift\Security\Authorization
 */
class AuthorizationRolesEnum extends Enum {

    public const NOT_LOGGED_IN = 'NOT_LOGGED_IN';
    public const LOGGED_IN = 'LOGGED_IN';

    public const ACCESS_GRANTED = 'ACCESS_GRANTED';
    public const ACCESS_DENIED = 'ACCESS_DENIED';

}