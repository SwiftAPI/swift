<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\RateLimiter\Factory;


interface RateLimiterConfigurationInterface {
    
    public function getName(): string;
    
    public function getStrategy(): string;
    
    public function getStateId(): string;
    
    public function getLimit(): int;
    
    public function getInterval(): \DateInterval;
    
}