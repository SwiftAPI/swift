<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization;

use Swift\HttpFoundation\Exception\AccessDeniedException;

/**
 * Interface AuthorizationCheckerInterface
 * @package Swift\Security\Authorization
 */
interface AuthorizationCheckerInterface {

    /**
     * Validate whether given subject should be available
     *
     * @param array $attributes
     * @param mixed $subject
     * @param string|null $strategy
     *
     * @return bool
     */
    public function isGranted( array $attributes, mixed $subject = null, string $strategy = null ): bool;

    /**
     * Validate whether given subject should be available
     * Throw exception when not granted
     *
     * @param array $attributes
     * @param mixed $subject
     * @param string|null $strategy
     *
     * @return void
     *
     * @throws AccessDeniedException
     */
    public function denyUnlessGranted( array $attributes, mixed $subject = null, string $strategy = null ): void;

}