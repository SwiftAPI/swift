<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization;


use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Container\Container;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authorization\Strategy\DecisionStrategyAffirmative;
use Swift\Security\Authorization\Strategy\DecisionStrategyConsensus;
use Swift\Security\Authorization\Strategy\DecisionStrategyPriority;
use Swift\Security\Authorization\Strategy\DecisionStrategyUnanimous;
use Swift\Security\Authorization\Voter\VoterInterface;

/**
 * Class AccessDecisionManager
 * @package Swift\Security\Authorization
 */
#[Autowire]
final class AccessDecisionManager implements AccessDecisionManagerInterface {

    public const STRATEGY_AFFIRMATIVE = DecisionStrategyAffirmative::class;
    public const STRATEGY_CONSENSUS = DecisionStrategyConsensus::class;
    public const STRATEGY_UNANIMOUS = DecisionStrategyUnanimous::class;
    public const STRATEGY_PRIORITY = DecisionStrategyPriority::class;

    /** @var VoterInterface[] $voters */
    private array $voters = array();

    /** @var array $decisionStrategies */
    private array $decisionStrategies;

    /**
     * AccessDecisionManager constructor.
     *
     * @param string $strategy
     */
    public function __construct(
        private string $strategy = self::STRATEGY_AFFIRMATIVE,
    ) {
    }


    /**
     * @inheritDoc
     */
    public function decide( TokenInterface $token, mixed $subject, array $attributes, string $decisionStrategy = null ): bool {
        // TODO: Implement decide() method.
    }

    #[Autowire]
    public function setVoters( #[Autowire(tag: DiTags::SECURITY_AUTHORIZATION_VOTER)] ?iterable $voters ): void {
        if (!$voters) {
            return;
        }

        foreach ($voters as $voter) {
            $this->voters[$voter::class] = $voter;
        }
    }

    #[Autowire]
    public function setDecisionStrategies( #[Autowire(tag: DiTags::SECURITY_AUTHORIZATION_DECISION_STRATEGY)] ?iterable $decisionStrategies ): void {
        if (!$decisionStrategies) {
            return;
        }

        foreach ($decisionStrategies as $decisionStrategy) {
            $this->decisionStrategies[$decisionStrategy::class] = $decisionStrategy;
        }
    }
}