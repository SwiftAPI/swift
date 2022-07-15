<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization;

/**
 * Class AuthorizationRole
 * @package Swift\Security\Authorization
 */
enum AuthorizationRole: string {

    // Main roles
    case ROLE_GUEST = 'ROLE_GUEST';
    case ROLE_USER = 'ROLE_USER';
    case ROLE_CLIENT = 'ROLE_CLIENT';
    case ROLE_ADMIN = 'ROLE_ADMIN';
    case ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    // Sub roles
    case ROLE_USERS_LIST = 'ROLE_USERS_LIST';
    case ROLE_CHANGE_PASSWORD = 'ROLE_CHANGE_PASSWORD';

}