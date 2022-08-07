<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\RateLimiter;

use Psr\Http\Message\ResponseInterface;
use Swift\DependencyInjection\Attributes\DI;
use Swift\HttpFoundation\Exception\TooManyRequestsException;
use Swift\HttpFoundation\JsonResponse;
use Swift\HttpFoundation\Response;

#[DI( autowire: false )]
class RateLimit implements RateLimitInterface {
    
    public function __construct(
        protected readonly int                $availableTokens,
        protected readonly bool               $isAccepted,
        protected readonly int                $limit,
        protected readonly \DateTimeInterface $resetTime,
    ) {
    }
    
    /**
     * @return int
     */
    public function getAvailableTokens(): int {
        return $this->availableTokens;
    }
    
    /**
     * @return bool
     */
    public function isAccepted(): bool {
        return $this->isAccepted;
    }
    
    /**
     * @return int
     */
    public function getLimit(): int {
        return $this->limit;
    }
    
    public function getResetTime(): \DateTimeInterface {
        return $this->resetTime;
    }
    
    public function denyIfNotAccepted(): void {
        if ( ! $this->isAccepted() ) {
            throw new TooManyRequestsException( $this );
        }
    }
    
    /**
     * Binds rate limit headers to a response.
     *
     * @param \Swift\Security\RateLimiter\RateLimitInterface|null $rateLimit
     * @param \Psr\Http\Message\ResponseInterface                 $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function bindToResponse( ?RateLimitInterface $rateLimit, ResponseInterface $response ): ResponseInterface {
        if ( ! $rateLimit ) {
            return $response;
        }
        
        return $response->withHeader( 'X-RateLimit-Limit', $rateLimit->getLimit() )
                        ->withHeader( 'X-RateLimit-Remaining', $rateLimit->getAvailableTokens() )
                        ->withHeader( 'X-RateLimit-Reset', $rateLimit->getResetTime()->getTimestamp() );
    }
    
    
}