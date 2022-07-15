<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping;


use Psr\Cache\CacheItemInterface;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Orm\Cache\EntityMappingCache;
use Swift\Orm\Mapping\Definition\Relation\EntitiesConnection;

#[Autowire]
class Registry implements RegistryInterface {
    
    public function __construct(
        protected EntityMappingCache $cache,
    ) {
    }
    
    public function getClassMetaData( string $name ): ?ClassMetaData {
        $cacheName = EntityMappingCache::serializeClassName( $name );
        $cacheItem = $this->cache->getItem($cacheName);
        
        return $cacheItem->isHit() ? $cacheItem->get() : null;
    }
    
    public function setClassMetaData( string $name, ClassMetaData $classMetaData ): void {
        $cacheName = EntityMappingCache::serializeClassName( $name );
        $cacheItem = $this->cache->getItem($cacheName);
        $cacheItem->set($classMetaData);
        $this->cache->save( $cacheItem );
    }
    
    public function getClassMetaDataCacheItem( string $name ): CacheItemInterface {
        $cacheName = EntityMappingCache::serializeClassName( $name );
    
        return $this->cache->getItem( $cacheName );
    }
    
    public function saveClassMetaDataCacheItem( CacheItemInterface $cacheItem ): void {
        $this->cache->save($cacheItem);
    }
    
    public function getEntitiesConnection( string $name ): ?EntitiesConnection {
        $cacheItem = $this->cache->getItem( $name );
        
        return $cacheItem->isHit() ? $cacheItem->get() : null;
    }
    
    public function setEntitiesConnection( EntitiesConnection $entitiesConnection ): void {
        $cacheItem = $this->cache->getItem( $entitiesConnection->getConnectionName() );
        $cacheItem->set($entitiesConnection);
        $this->cache->save( $cacheItem );
    }
    
    public function getEntitiesConnectionCacheItem( array $names ): CacheItemInterface {
        return $this->cache->getItem( EntitiesConnection::namesToConnectionName( $names) );
    }
    
    public function saveEntitiesConnectionCacheItem( CacheItemInterface $cacheItem ): void {
        $this->cache->save($cacheItem);
    }
    
    public function hasEntitiesConnection( array $names ): bool {
        return $this->cache->getItem( EntitiesConnection::namesToConnectionName( $names) )->isHit();
    }
    
}