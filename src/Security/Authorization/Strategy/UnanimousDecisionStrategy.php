<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization\Strategy;


use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authorization\Voter\VoterInterface;

/**
 * Class UnanimousDecisionStrategy
 * @package Swift\Security\Authorization\Strategy
 */
#[Autowire]
class UnanimousDecisionStrategy implements DecisionStrategyInterface {

    /**
     * Grants access if only grant (ignoring abstain) votes were received.
     *
     * @inheritDoc
     */
    public function decide( array $voters, TokenInterface $token, mixed $subject, array $attributes, bool $allowIfAllAbstain = false ): bool {
        $grant = 0;
        foreach ($voters as $voter) {
            $result = $voter->vote($token, $subject, $attributes);

            if (VoterInterface::ACCESS_DENIED === $result) {
                return false;
            }

            if (VoterInterface::ACCESS_GRANTED === $result) {
                $grant++;
            }
        }

        return $grant > 0; // At least one voter should have participated and claimed true
    }
}