<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\RateLimiter\Storage;


use Swift\Orm\Dbal\ResultCollectionInterface;

class DatabaseTokenBucket implements \Swift\Security\RateLimiter\TokenBucketInterface {
    
    public function __construct(
        protected string                    $name,
        protected string                    $stateId,
        protected ResultCollectionInterface $tokens,
    ) {
    }
    
    public function getName(): string {
        return $this->name;
    }
    
    public function getStateId(): string {
        return $this->stateId;
    }
    
    /**
     * @param int $tokens
     *
     * @return void
     */
    public function consume( int $tokens ): void {
        $token = ( new LimiterTokenEntity() )
            ->setRateName( $this->name )
            ->setStateId( $this->stateId )
            ->setTokens( $tokens );
        
        $this->tokens->addMany( [ $token ] );
    }
    
    public function getConsumedTokensCount( ?\DateTimeInterface $after = null ): int {
        $num = 0;
        
        foreach ( $this->tokens as $token ) {
            /** @var \Swift\Security\RateLimiter\Storage\LimiterTokenEntity $token */
            
            if ( $token->getCreatedAt() < $after ) {
                continue;
            }
            
            $num += $token->getTokens();
        }
        
        return $num;
    }
    
    public function getTokens(): ResultCollectionInterface {
        return $this->tokens;
    }
    
}