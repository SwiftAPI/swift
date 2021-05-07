<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication;

/**
 * Class AuthenticationEvents
 * @package Swift\Security\Authentication
 */
class AuthenticationEvents {

    public const AUTHENTICATION_SUCCESS = 'security.authentication.success';
    public const AUTHENTICATION_FAILURE = 'security.authentication.failure';

}