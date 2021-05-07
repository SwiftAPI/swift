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
 * Class DiTags
 * @package Swift\Security\Authentication
 */
class DiTags {

    public const SECURITY_AUTHENTICATOR = 'security.authentication.authenticator';
    public const SECURITY_AUTHENTICATOR_ENTRYPOINT = 'security.authentication.authenticator.entrypoint';
    public const SECURITY_TOKEN_PROVIDER = 'security.authentication.token.provider';
    public const SECURITY_TOKEN_STORAGE = 'security.authentication.token.storage';
    public const SECURITY_USER_PROVIDER = 'security.user.provider';

}