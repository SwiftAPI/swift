<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem\Watcher\ResourceWatcherBased;


class ResourceWatcherResult {
    
    private bool $hasChanges = false;
    
    /**
     * Constructor.
     */
    public function __construct(
        private readonly array $newResources,
        private readonly array $deletedResources,
        private readonly array $updatedResources
    ) {
    }
    
    /**
     * Has any change in resources?
     *
     * @return bool
     */
    public function hasChanges(): bool {
        if ( $this->hasChanges ) {
            return $this->hasChanges;
        }
        
        $this->hasChanges = ( count( $this->newResources ) > 0 ) || ( count( $this->deletedResources ) > 0 ) || ( count( $this->updatedResources ) > 0 );
        
        return $this->hasChanges;
    }
    
    /**
     * Returns an array with paths of the new resources ('.', '..' not resolved).
     *
     * @return array
     */
    public function getNewFiles(): array {
        return $this->newResources;
    }
    
    /**
     * Returns an array with path of the deleted resources ('.', '..' not resolved).
     *
     * @return array
     */
    public function getDeletedFiles(): array {
        return $this->deletedResources;
    }
    
    /**
     * Returns an array with path of the updated resources ('.', '..' not resolved).
     *
     * @return array
     */
    public function getUpdatedFiles(): array {
        return $this->updatedResources;
    }
    
}