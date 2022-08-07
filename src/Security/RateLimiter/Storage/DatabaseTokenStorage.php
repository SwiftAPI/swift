<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\RateLimiter\Storage;


use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Security\RateLimiter\TokenBucketInterface;

#[Autowire]
class DatabaseTokenStorage implements StorageInterface {
    
    public function __construct(
        protected \Swift\Orm\EntityManagerInterface $entityManager,
    ) {
    }
    
    public function persist( TokenBucketInterface $tokenBucket ): void {
        foreach ( $tokenBucket->getTokens() as $token ) {
            $this->entityManager->persist( $token );
        }
        
        $this->entityManager->run();
    }
    
    public function fetch( string $name, string $stateId ): ?TokenBucketInterface {
        $tokens = $this->entityManager->findMany( LimiterTokenEntity::class, [
            'rateName' => $name,
            'stateId'  => $stateId,
        ] );
        
        return new DatabaseTokenBucket( $name, $stateId, $tokens );
    }
    
    public function reset( string $name, string $stateId, ?\DateTimeInterface $before = null ): void {
        $tokens = $this->entityManager->findMany( LimiterTokenEntity::class, [
            'rateName' => $name,
            'stateId'  => $stateId,
        ] );
        
        foreach ( $tokens as $token ) {
            if ( ( $before !== null ) && ( $token->getCreatedAt() > $before ) ) {
                continue;
            }
            
            $this->entityManager->delete( $token );
        }
        $this->entityManager->run();
    }
    
}