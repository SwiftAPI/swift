<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization;


use Swift\Configuration\ConfigurationInterface;
use Swift\Kernel\Attributes\Autowire;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authorization\Strategy\AffirmativeDecisionStrategy;
use Swift\Security\Authorization\Strategy\ConsensusDecisionStrategy;
use Swift\Security\Authorization\Strategy\DecisionStrategyInterface;
use Swift\Security\Authorization\Strategy\PriorityDecisionStrategy;
use Swift\Security\Authorization\Strategy\UnanimousDecisionStrategy;
use Swift\Security\Authorization\Voter\VoterInterface;

/**
 * Class AccessDecisionManager
 * @package Swift\Security\Authorization
 */
#[Autowire]
final class AccessDecisionManager implements AccessDecisionManagerInterface {

    public const STRATEGY_AFFIRMATIVE = AffirmativeDecisionStrategy::class;
    public const STRATEGY_CONSENSUS = ConsensusDecisionStrategy::class;
    public const STRATEGY_UNANIMOUS = UnanimousDecisionStrategy::class;
    public const STRATEGY_PRIORITY = PriorityDecisionStrategy::class;

    /** @var VoterInterface[] $voters */
    private array $voters = array();

    /** @var DecisionStrategyInterface[] $decisionStrategies */
    private array $decisionStrategies;

    /**
     * AccessDecisionManager constructor.
     *
     * @param ConfigurationInterface $configuration
     */
    public function __construct(
        private ConfigurationInterface $configuration,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function decide( TokenInterface $token, mixed $subject, array $attributes, string $strategy = null ): bool {
        $strategy ??= $this->configuration->get('access_decision_manager.strategy', 'security');
        if (!array_key_exists(key: $strategy, array: $this->decisionStrategies)) {
            throw new \InvalidArgumentException(sprintf('Decision strategy %s is not found. Please register this strategy or choose another', $strategy));
        }

        return $this->decisionStrategies[$strategy]->decide(
            $this->voters,
            $token,
            $subject,
            $attributes,
            $this->configuration->get('access_decision_manager.allow_if_all_abstain', 'security')
        );
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