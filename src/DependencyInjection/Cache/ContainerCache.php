<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\DependencyInjection\Cache;


use League\Flysystem\FilesystemException;
use Swift\Cache\AbstractFileCache;
use Swift\FileSystem\FileSystem;

class ContainerCache extends AbstractFileCache {
    
    
    public function __construct() {
        parent::__construct( 0, new ContainerCacheMarshaller() );
    }
    
    public function getNameSpace(): string {
        return 'di';
    }
    
    public function getName(): string {
        return 'container';
    }
    
    public function clear( string $prefix = '' ): bool {
        $fileSystem = new FileSystem();
        
        if ($fileSystem->exists( '/var/cache/di/container.php' )) {
            try {
                $fileSystem->delete( '/var/cache/di/container.php' );
            } catch (FileSystemException) {
                return false;
            }
        }
        
        return parent::clear( $prefix );
    }
    
}