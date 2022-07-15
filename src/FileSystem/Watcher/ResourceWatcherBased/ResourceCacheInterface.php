<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem\Watcher\ResourceWatcherBased;


interface ResourceCacheInterface {
    
    /**
     * If the cache Initialized? if not then warm-up cache.
     *
     * @return bool
     */
    public function isInitialized(): bool;
    
    /**
     * Returns the hash of a file in cache.
     *
     * @param string $filename
     *
     * @return string The hash for the filename. Empty string if not exists.
     */
    public function read( string $filename): string;
    
    /**
     * Updates the hash of a file in cache.
     *
     * @param string $filename
     * @param string $hash The calculated hash for the filename.
     */
    public function write( string $filename, string $hash): void;
    
    /**
     * Deletes a file in cache.
     *
     * @param string $filename
     *
     * @return void
     */
    public function delete( string $filename): void;
    
    /**
     * Erases all the elements in cache.
     *
     * @return void
     */
    public function erase(): void;
    
    /**
     * Returns all the element in cache.
     *
     * @return array A key-value array in which the key is the filename and the value is the hash.
     */
    public function getAll(): array;
    
    /**
     * Persists the cache
     *
     * @return void
     */
    public function save(): void;
    
}