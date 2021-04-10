<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization\Voter;

use Swift\Kernel\Attributes\DI;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authorization\DiTags;

/**
 * Class VoterInterface
 * @package Swift\Security\Authorization\Voter
 */
#[DI(tags: [DiTags::SECURITY_AUTHORIZATION_VOTER])]
interface VoterInterface {

    public const ACCESS_GRANTED = 'ACCESS_GRANTED';
    public const ACCESS_DENIED = 'ACCESS_DENIED';
    public const ACCESS_ABSTAIN = 'ACCESS_ABSTAIN';

    /**
     * Vote
     *
     * @param TokenInterface $token
     * @param mixed $subject
     * @param array $attributes
     *
     * @return string ACCESS_GRANTED || ACCESS_DENIED || ACCESS_ABSTAIN
     */
    public function vote( TokenInterface $token, mixed $subject, array $attributes ): string;

}