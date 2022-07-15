<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Swift\Cache\Adapter\FilesystemAdapter;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\DependencyInjection\Attributes\DI;
use Swift\Kernel\KernelDiTags;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Cache\Marshaller\MarshallerInterface;

#[Autowire, DI( tags: [KernelDiTags::CACHE_TYPE ])]
abstract class AbstractCache extends ChainAdapter implements CacheItemPoolInterface {
    
    public function __construct(
        int $defaultLifetime = 0,
        MarshallerInterface $marshaller = null,
        ?array $adapters = null,
    ) {
        parent::__construct(
            $adapters ?: [
                new ArrayAdapter( 1000000, false, 1000000, 1000000 ),
                new FilesystemAdapter('', $defaultLifetime, INCLUDE_DIR . '/var/cache/' . $this->getNameSpace() . '/', $marshaller),
            ]
        );
    }
    
    abstract public function getName(): string;
    
    abstract public function getNameSpace(): string;
    
    public function getFullName(): string {
        return $this->getNameSpace() . '.' . $this->getName();
    }
    
    public static function serializeClassName( string $className ): string {
        return str_replace( '\\', '__', $className);
    }
    
    /**
     * @inheritDoc
     */
    public function hasItem( $key ): bool {
        return parent::hasItem( $key );
    }
    
    /**
     * @inheritDoc
     */
    public function clear( string $prefix = '' ): bool {
        return parent::clear( $prefix );
    }
    
    /**
     * @inheritDoc
     */
    public function deleteItem( $key ): bool {
        return parent::deleteItem( $key );
    }
    
    /**
     * @inheritDoc
     */
    public function deleteItems( array $keys ): bool {
        return parent::deleteItems( $keys );
    }
    
    /**
     * @inheritDoc
     */
    public function getItem( $key ): CacheItem {
        return parent::getItem( $key );
    }
    
    /**
     * @inheritDoc
     *
     * @return CacheItemInterface[]
     */
    public function getItems( array $keys = [] ): \Generator {
        return parent::getItems( $keys );
    }
    
    /**
     * @inheritDoc
     */
    public function save( CacheItemInterface $item ): bool {
        return parent::save( $item );
    }
    
    /**
     * @inheritDoc
     */
    public function saveDeferred( CacheItemInterface $item ): bool {
        return parent::saveDeferred( $item );
    }
    
    
    
}