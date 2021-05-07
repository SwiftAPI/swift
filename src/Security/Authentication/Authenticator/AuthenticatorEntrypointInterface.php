<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Authenticator;

use Swift\Kernel\Attributes\DI;
use Swift\Security\Authentication\DiTags;

/**
 * Interface AuthenticatorEntrypointInterface
 * @package Swift\Security\Authentication\Authenticator
 */
#[DI(tags: [DiTags::SECURITY_AUTHENTICATOR, DiTags::SECURITY_AUTHENTICATOR_ENTRYPOINT])]
interface AuthenticatorEntrypointInterface {

}