<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization\Voter;


use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Security\Authentication\AuthenticationTypeResolverInterface;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authorization\AuthorizationType;

/**
 * Class AuthenticatedVoter
 * @package Swift\Security\Authorization\Voter
 */
#[Autowire]
class AuthenticatedVoter implements VoterInterface {
    
    /**
     * AuthenticatedVoter constructor.
     *
     * @param AuthenticationTypeResolverInterface $authenticationTypeResolver
     */
    public function __construct(
        private readonly AuthenticationTypeResolverInterface $authenticationTypeResolver,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public function vote( TokenInterface $token, mixed $subject, array $attributes ): Vote {
        $vote = Vote::ACCESS_ABSTAIN;
        
        if ( in_array( AuthorizationType::PUBLIC_ACCESS, $attributes, true ) ) {
            return Vote::ACCESS_GRANTED;
        }
        
        foreach ( $attributes as $attribute ) {
            // Default to no access unless one of the below conditions proves right
            $vote = Vote::ACCESS_DENIED;
            
            $attribute = ! is_string( $attribute ) && enum_exists( $attribute::class ) ? $attribute->value : $attribute;
            
            if ( ( AuthorizationType::IS_AUTHENTICATED->value === $attribute ) && $this->authenticationTypeResolver->isAuthenticated() ) {
                return Vote::ACCESS_GRANTED;
            }
            
            if ( ( AuthorizationType::IS_AUTHENTICATED_ANONYMOUSLY->value === $attribute ) && $this->authenticationTypeResolver->isAnonymous() ) {
                return Vote::ACCESS_GRANTED;
            }
            
            if ( ( AuthorizationType::IS_AUTHENTICATED_TOKEN->value === $attribute ) && $this->authenticationTypeResolver->isPreAuthenticated() ) {
                return Vote::ACCESS_GRANTED;
            }
            
            if ( ( AuthorizationType::IS_AUTHENTICATED_DIRECTLY->value === $attribute ) && $this->authenticationTypeResolver->isDirectLogin() ) {
                return Vote::ACCESS_GRANTED;
            }
        }
        
        return $vote;
    }
}