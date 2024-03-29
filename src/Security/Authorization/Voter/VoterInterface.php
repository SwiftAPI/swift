<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization\Voter;

use Swift\DependencyInjection\Attributes\DI;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authorization\DiTags;

/**
 * Class VoterInterface
 * @package Swift\Security\Authorization\Voter
 */
#[DI(tags: [DiTags::SECURITY_AUTHORIZATION_VOTER])]
interface VoterInterface {

    public const ACCESS_GRANTED = Vote::ACCESS_GRANTED;
    public const ACCESS_DENIED = Vote::ACCESS_DENIED;
    public const ACCESS_ABSTAIN = Vote::ACCESS_ABSTAIN;
    
    /**
     * Vote
     *
     * @param TokenInterface $token
     * @param mixed          $subject
     * @param array          $attributes
     *
     * @return \Swift\Security\Authorization\Voter\Vote ACCESS_GRANTED || ACCESS_DENIED || ACCESS_ABSTAIN
     */
    public function vote( TokenInterface $token, mixed $subject, array $attributes ): Vote;

}