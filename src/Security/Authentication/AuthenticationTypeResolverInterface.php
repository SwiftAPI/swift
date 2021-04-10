<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication;

/**
 * Interface AuthenticationTypeResolverInterface
 * @package Swift\Security\Authentication
 */
interface AuthenticationTypeResolverInterface {

    /**
     * Check whether user logged in using a token
     *
     * @return bool
     */
    public function isPreAuthenticated(): bool;

    /**
     * Check if user is anonymous user
     *
     * @return bool
     */
    public function isAnonymous(): bool;

    /**
     * Check if user logged in during the current request
     *
     * @return bool
     */
    public function isDirectLogin(): bool;

    /**
     * Check if user is logged in
     *
     * @return bool
     */
    public function isAuthenticated(): bool;

}