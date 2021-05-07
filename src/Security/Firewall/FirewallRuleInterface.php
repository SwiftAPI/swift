<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Firewall;

use Swift\HttpFoundation\RequestInterface;
use Swift\Kernel\Attributes\DI;
use Swift\Security\Authentication\Token\TokenInterface;

/**
 * Interface FirewallRuleInterface
 * @package Swift\Security\Firewall
 */
#[DI(tags: [DiTags::SECURITY_FIREWALL_RULE], shared: false)]
interface FirewallRuleInterface {

    /**
     * Validate token and/or request
     *
     * @param TokenInterface $token
     * @param RequestInterface $request
     */
    public function validate( TokenInterface $token, RequestInterface $request ): void;

    /**
     * Get sub conditions that must be true before this rule might apply
     *
     * @return FirewallRuleInterface[]
     */
    public function getConditionals(): array;

}