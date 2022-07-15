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
use Swift\Orm\Mapping\Definition\Relation\EntitiesConnection;


interface RegistryInterface {
    
    public function getClassMetaData( string $name ): ?ClassMetaData;
    
    public function setClassMetaData( string $name, ClassMetaData $classMetaData );
    
    public function getClassMetaDataCacheItem( string $name ): CacheItemInterface;
    
    public function saveClassMetaDataCacheItem( CacheItemInterface $cacheItem ): void;
    
    public function getEntitiesConnection( string $name ): ?EntitiesConnection;
    
    public function setEntitiesConnection( EntitiesConnection $entitiesConnection ): void;
    
    public function getEntitiesConnectionCacheItem( array $names ): CacheItemInterface;
    
    public function saveEntitiesConnectionCacheItem( CacheItemInterface $cacheItem ): void;
    
    public function hasEntitiesConnection( array $names ): bool;
    
    
    
}