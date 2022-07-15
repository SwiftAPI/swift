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
 * Class AuthorizationTypes
 * @package Swift\Security\Authorization
 */
enum AuthorizationType: string {

    case IS_AUTHENTICATED = 'IS_AUTHENTICATED';
    case IS_AUTHENTICATED_DIRECTLY = 'IS_AUTHENTICATED_DIRECTLY';
    case IS_AUTHENTICATED_ANONYMOUSLY = 'IS_AUTHENTICATED_ANONYMOUSLY';
    case IS_AUTHENTICATED_TOKEN = 'IS_AUTHENTICATED_TOKEN';
    case PUBLIC_ACCESS = 'PUBLIC_ACCESS';

}