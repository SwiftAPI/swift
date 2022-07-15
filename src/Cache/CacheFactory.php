<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Cache;

use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Kernel\KernelDiTags;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

#[Autowire]
class CacheFactory {
    
    /** @var \Swift\Cache\AbstractCache[] */
    private array $caches;
    
    public function create( string $cacheClassName ): ?AbstractCache {
        return $this->caches[$cacheClassName] ?? null;
    }
    
    /**
     * @param \Swift\Cache\AbstractCache[] $caches
     */
    public function setCaches( #[Autowire( tag: KernelDiTags::CACHE_TYPE )] iterable $caches ): void {
        if (empty($caches)) {
            return;
        }
        
        foreach ($caches as $cache) {
            $this->caches[$cache::class] = $cache;
        }
    }
    
}