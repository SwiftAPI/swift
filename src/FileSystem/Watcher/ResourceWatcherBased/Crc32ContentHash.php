<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem\Watcher\ResourceWatcherBased;


class Crc32ContentHash implements HashInterface {
    
    /**
     * @inheritDoc
     */
    public function hash( string $filepath ): string {
        $fileContent = $filepath;
        
        if ( ! \is_dir( $filepath ) ) {
            $fileContent = file_get_contents( $filepath );
        }
        
        return hash( 'crc32', $fileContent );
    }
    
}