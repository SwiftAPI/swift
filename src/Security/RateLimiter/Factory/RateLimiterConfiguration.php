<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\RateLimiter\Factory;


class RateLimiterConfiguration implements RateLimiterConfigurationInterface {
    
    public function __construct(
        protected readonly string $name,
        protected readonly string $strategy,
        protected readonly string $stateId,
        protected readonly int $limit,
        protected readonly \DateInterval $dateInterval,
    ) {
    }
    
    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }
    
    /**
     * @return string
     */
    public function getStrategy(): string {
        return $this->strategy;
    }
    
    /**
     * @return string
     */
    public function getStateId(): string {
        return $this->stateId;
    }
    
    /**
     * @return int
     */
    public function getLimit(): int {
        return $this->limit;
    }
    
    /**
     * @return \DateInterval
     */
    public function getInterval(): \DateInterval {
        return $this->dateInterval;
    }
    

}