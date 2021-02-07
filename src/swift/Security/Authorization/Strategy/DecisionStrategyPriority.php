<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization\Strategy;


use Swift\Kernel\Attributes\Autowire;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authorization\Voter\VoterInterface;

/**
 * Class DecisionStrategyPriority
 * @package Swift\Security\Authorization\Strategy
 */
#[Autowire]
class DecisionStrategyPriority implements DecisionStrategyInterface {

    /**
     * @inheritDoc
     */
    public function decide( array $voters, TokenInterface $token, mixed $subject, array $attributes ): bool {
        foreach ($voters as $voter) {
            $result = $voter->vote($token, $subject, $attributes);

            if (VoterInterface::ACCESS_GRANTED === $result) {
                return true;
            }

            if (VoterInterface::ACCESS_DENIED === $result) {
                return false;
            }
        }

        return false;
    }
}