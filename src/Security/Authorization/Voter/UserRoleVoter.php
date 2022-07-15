<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization\Voter;


use Swift\Configuration\ConfigurationInterface;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authorization\AuthorizationRole;
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
        protected ConfigurationInterface $configuration,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public function vote( TokenInterface $token, mixed $subject, array $attributes ): Vote {
        $vote = Vote::ACCESS_ABSTAIN;
        
        if ( in_array( AuthorizationRole::ROLE_GUEST, $attributes, true ) ) {
            return Vote::ACCESS_GRANTED;
        }
        
        $config = $this->configuration->get( 'role_hierarchy', 'security' );
        
        if ( ! $config ) {
            return $vote;
        }
        
        $rolesBag = new UserRolesBag( $this->getAllRoles( $config, $token->getUser()->getRoles()->getIterator()->getArrayCopy() ) ?? [] );
        
        foreach ( $attributes as $attribute ) {
            $vote = Vote::ACCESS_DENIED;
            
            $attribute = ! is_string( $attribute ) && enum_exists( $attribute::class ) ? $attribute->value : $attribute;
            
            if ( $rolesBag->has( $attribute ) ) {
                return Vote::ACCESS_GRANTED;
            }
        }
        
        return $vote;
    }
    
    /**
     * Resolve role hierarchy and fetch all roles the user has based on hierarchy or relations
     *
     * @param array $config
     * @param array $roles
     *
     * @return array|null
     */
    private function getAllRoles( array $config, array $roles ): array|null {
        if ( empty( $roles ) ) {
            return null;
        }
        
        $related = [];
        
        foreach ( $roles as $role ) {
            $related[ $role ] = $role;
            if ( array_key_exists( $role, $config ) ) {
                $result = $this->getAllRoles( $config, $config[ $role ] );
                $related = $result ? array_merge( $related, $result ) : $related;
            }
        }
        
        return $related;
    }
    
}