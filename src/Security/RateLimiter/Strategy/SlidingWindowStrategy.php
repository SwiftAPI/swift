<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\RateLimiter\Strategy;


use Swift\DependencyInjection\Attributes\DI;
use Swift\Security\RateLimiter\Exception\BucketNotFoundException;
use Swift\Security\RateLimiter\RateLimit;
use Swift\Security\RateLimiter\RateLimitInterface;
use Swift\Security\RateLimiter\Storage\StorageInterface;

#[DI( autowire: false, aliases: [ 'security.rate_limiter.strategy.sliding_window' ] )]
class SlidingWindowStrategy implements \Swift\Security\RateLimiter\RateLimiterInterface {
    
    public const NAME = 'sliding_window';
    
    public function __construct(
        protected string           $name,
        protected string           $stateId,
        protected int              $limit,
        protected \DateInterval    $dateInterval,
        protected StorageInterface $storage,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public function consume( int $tokens = 1 ): RateLimitInterface {
        $borderDate = new \DateTimeImmutable();
        $bucket     = $this->storage->fetch( $this->name, $this->stateId );
        
        if ( ! $bucket ) {
            throw new BucketNotFoundException( sprintf( 'Could not find bucket with name %s', $this->name ) );
        }
        
        $consumed = $bucket->getConsumedTokensCount( ( clone $borderDate )->sub( $this->dateInterval ) );
        
        if ( ( $consumed + $tokens ) > $this->limit ) {
            return new RateLimit(
                $this->limit - $consumed,
                false,
                $this->limit,
                ( clone $borderDate )->add( $this->dateInterval )
            );
        }
        
        $bucket->consume( $tokens );
        
        $this->storage->persist( $bucket );
        $this->storage->reset( $this->name, $this->stateId, ( clone $borderDate )->sub( $this->dateInterval ) );
        
        return new RateLimit(
            $this->limit - $consumed - $tokens,
            true,
            $this->limit,
            ( clone $borderDate )->add( $this->dateInterval )
        );
    }
    
    /**
     * @inheritDoc
     */
    public function reset(): void {
        $this->storage->reset( $this->name, self::NAME, $this->stateId );
    }
    
}