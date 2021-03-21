<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization\Voter;


use Swift\Configuration\ConfigurationInterface;
use Swift\Kernel\Attributes\Autowire;
use Swift\Security\Authentication\AuthenticationTypeResolverInterface;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authorization\AuthorizationRolesEnum;
use Swift\Security\Authorization\AuthorizationTypesEnum;
use Swift\Security\User\UserRolesBag;

/**
 * Class UserRoleVoter
 * @package Swift\Security\Authorization\Voter
 */
#[Autowire]
class UserRoleVoter implements VoterInterface {

    /**
     * UserRoleVoter constructor.
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
    public function vote( TokenInterface $token, mixed $subject, array $attributes ): string {
        $vote = VoterInterface::ACCESS_ABSTAIN;

        if (in_array(AuthorizationRolesEnum::ROLE_GUEST, $attributes, true)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        $rolesBag = new UserRolesBag($this->getAllRoles($token->getUser()->getRoles()->getIterator()->getArrayCopy()) ?? array());

        foreach ($attributes as $attribute) {
            // Abstain on non supported attributes
            if (!AuthorizationRolesEnum::isValid($attribute)) {
                continue;
            }

            $vote = VoterInterface::ACCESS_DENIED;

            if ($rolesBag->has($attribute)) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        return $vote;
    }

    /**
     * Resolve role hierarchy and fetch all roles the user has based on hierarchy or relations
     *
     * @param array $roles
     *
     * @return array|null
     */
    private function getAllRoles( array $roles ): array|null {
        $config = $this->configuration->get('role_hierarchy', 'security');

        if (!is_array($config) || empty($roles)) {
            return null;
        }

        $related = array();

        foreach ($roles as $role) {
            $related[$role] = $role;
            if (array_key_exists($role, $config)) {
                $result = $this->getAllRoles($config[$role]);
                $related = $result ? array_merge($related, $result) : $related;
            }
        }

        return $related;
    }

}