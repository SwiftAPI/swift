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

    // Main roles
    public const ROLE_GUEST = 'ROLE_GUEST';
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_CLIENT = 'ROLE_CLIENT';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    // Sub roles
    public const ROLE_USERS_LIST = 'ROLE_USERS_LIST';
    public const ROLE_CHANGE_PASSWORD = 'ROLE_CHANGE_PASSWORD';

}