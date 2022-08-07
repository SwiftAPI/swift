<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\RateLimiter\Storage;



interface StorageInterface {
    
    public function persist( \Swift\Security\RateLimiter\TokenBucketInterface $tokenBucket ): void;
    
    public function fetch( string $name, string $stateId ): ?\Swift\Security\RateLimiter\TokenBucketInterface;
    
    public function reset( string $name, string $stateId ): void;
    
}