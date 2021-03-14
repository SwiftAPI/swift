<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization\Voter;


use Swift\Kernel\Attributes\Autowire;
use Swift\Security\Authentication\AuthenticationTypeResolverInterface;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authorization\AuthorizationRolesEnum;
use Swift\Security\Authorization\AuthorizationTypesEnum;

/**
 * Class UserRoleVoter
 * @package Swift\Security\Authorization\Voter
 */
#[Autowire]
class UserRoleVoter implements VoterInterface {

    /**
     * @inheritDoc
     */
    public function vote( TokenInterface $token, mixed $subject, array $attributes ): string {
        $vote = VoterInterface::ACCESS_ABSTAIN;

        if (in_array(AuthorizationRolesEnum::ROLE_GUEST, $attributes, true)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        foreach ($attributes as $attribute) {
            // Abstain on non supported attributes
            if (!AuthorizationRolesEnum::isValid($attribute)) {
                continue;
            }

            $vote = VoterInterface::ACCESS_DENIED;

            if ($token->getUser()->getRoles()->has($attribute)) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        return $vote;
    }
}