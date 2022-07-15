<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem\Watcher\ResourceWatcherBased;


class ResourceCacheMemory implements ResourceCacheInterface {
    
    protected bool $isInitialized = false;
    private array $data = [];
    
    /**
     * @inheritDoc
     */
    public function isInitialized(): bool {
        return $this->isInitialized;
    }
    
    /**
     * @inheritDoc
     */
    public function read( string $filename ): string {
        return $this->data[ $filename ] ?? '';
    }
    
    /**
     * @inheritDoc
     */
    public function write( string $filename, string $hash ): void {
        $this->data[ $filename ] = $hash;
        $this->isInitialized     = true;
    }
    
    /**
     * @inheritDoc
     */
    public function delete( string $filename ): void {
        unset( $this->data[ $filename ] );
    }
    
    /**
     * @inheritDoc
     */
    public function erase(): void {
        $this->data = [];
    }
    
    /**
     * @inheritDoc
     */
    public function getAll(): array {
        return $this->data;
    }
    
    /**
     * @inheritDoc
     */
    public function save(): void {
        $this->isInitialized = true;
    }
}