<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization;

use Swift\Security\Authentication\Token\TokenInterface;

/**
 * Interface AccessDecisionManagerInterface
 * @package Swift\Security\Authorization
 */
interface AccessDecisionManagerInterface {

    /**
     * Decide whether access should be granted
     *
     * @param TokenInterface $token Token to vote against
     * @param mixed $subject Subject to vote on
     * @param array $attributes Decision attributes
     * @param string|null $strategy Override default strategy
     *
     * @return bool
     */
    public function decide( TokenInterface $token, mixed $subject, array $attributes, string $strategy = null ): bool;

}