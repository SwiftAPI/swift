<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization\Strategy;

use Swift\DependencyInjection\Attributes\DI;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authorization\DiTags;
use Swift\Security\Authorization\Voter\VoterInterface;

/**
 * Interface DecisionStrategyInterface
 * @package Swift\Security\Authorization\Strategy
 */
#[DI(tags: [DiTags::SECURITY_AUTHORIZATION_DECISION_STRATEGY])]
interface DecisionStrategyInterface {

    /**
     * @param VoterInterface[] $voters
     * @param TokenInterface $token
     * @param mixed $subject
     * @param array $attributes
     * @param bool $allowIfAllAbstain
     *
     * @return mixed
     */
    public function decide( array $voters, TokenInterface $token, mixed $subject, array $attributes, bool $allowIfAllAbstain = false ): bool;

}