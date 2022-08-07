<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\RateLimiter;


interface RateLimitInterface {
    
    /**
     * Number of remaining tokens.
     *
     * @return int
     */
    public function getAvailableTokens(): int;
    
    /**
     * Whether the request is accepted.
     *
     * @return bool
     */
    public function isAccepted(): bool;
    
    /**
     * Limit of requests.
     *
     * @return int
     */
    public function getLimit(): int;
    
    /**
     * Time at which the limit will be reset.
     *
     * @return \DateTimeInterface
     */
    public function getResetTime(): \DateTimeInterface;
    
    /**
     * Throws an exception if the rate limit is not accepted.
     *
     * @return void
     */
    public function denyIfNotAccepted(): void;
    
}