<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Cache;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\Psr6\CacheAdapter;
use Swift\Configuration\ConfigurationInterface;
use Swift\Kernel\Attributes\Autowire;
use Symfony\Component\Cache\Adapter\DoctrineAdapter;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class CacheFactory
 * @package Swift\Cache
 */
#[Autowire]
class CacheFactory {

    private const CACHE_DIR = INCLUDE_DIR . '/var/cache/';

    /**
     * CacheFactory constructor.
     */
    public function __construct(
        private ConfigurationInterface $configuration,
        private array $instances = array(),
    ) {
    }

    public function create( string $cacheName ): Psr6CacheBridge {
        // No cache driver just yet, so instance it
        if ( ! array_key_exists($cacheName, $this->instances) ) {
            $this->instances[$cacheName] = $this->isDevMode() ? new Psr6CacheBridge(new ArrayCache()) : new Psr6CacheBridge($this->createFileCacheDriver($cacheName));

            return $this->instances[$cacheName];
        }

        $instance = $this->instances[$cacheName];
        $instanceUnwrapped = is_a($instance, Psr6CacheBridge::class, true) ? $instance->getCache() : $instance;

        // Check if a driver is created and matches the current settings
        if ( $this->isDevMode() && is_a( $instanceUnwrapped, ArrayCache::class, true ) ) {
            return $instance;
        }
        if ( ! $this->isDevMode() && is_a( $instanceUnwrapped, ChainCache::class, true ) ) {
            return $instance;
        }

        // There was a driver already, but the settings have changed, so let's instantiate a new driver
        return $this->isDevMode() ? new Psr6CacheBridge(new ArrayCache()) : new Psr6CacheBridge($this->createFileCacheDriver());
    }

    public function createDoctrineCache( string $cacheName ): Cache {
        // No cache driver just yet, so instance it
        if ( ! array_key_exists($cacheName, $this->instances) ) {
            $this->instances[$cacheName] = $this->isDevMode() ? new ArrayCache() : $this->createFileCacheDriver($cacheName);

            return $this->instances[$cacheName];
        }

        $instance = $this->instances[$cacheName];

        // Check if a driver is created and matches the current settings
        if ( $this->isDevMode() && is_a( $instance, ArrayCache::class, true ) ) {
            return $instance;
        }
        if ( ! $this->isDevMode() && is_a( $instance, ChainCache::class, true ) ) {
            return $instance;
        }

        // There was a driver already, but the settings have changed, so let's instantiate a new driver
        return $this->isDevMode() ? new ArrayCache() : $this->createFileCacheDriver();
    }

    private function createFileCacheDriver( string $cacheName ): Cache {
        $fileSystem = new Filesystem();
        if ( ! $fileSystem->exists( self::CACHE_DIR ) ) {
            $fileSystem->mkdir( self::CACHE_DIR );
        }

        return new ChainCache( [
            new ArrayCache(),
            new PhpFileCache( self::CACHE_DIR, $cacheName ),
        ] );
    }

    private function isDevMode(): bool {
        return $this->configuration->get( 'app.mode', 'app' ) === 'develop';
    }

}