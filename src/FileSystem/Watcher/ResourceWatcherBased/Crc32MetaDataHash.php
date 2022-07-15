<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem\Watcher\ResourceWatcherBased;


class Crc32MetaDataHash implements HashInterface {
    
    /**
     * Assign option to clear the file stat() cache.
     *
     * @param bool $clearStatCache
     */
    public function __construct(
        protected  bool $clearStatCache = false
    ) {
    }
    
    /**
     * {@inheritdoc}
     */
    public function hash( $filepath ): string {
        if ( $this->clearStatCache ) {
            clearstatcache( true, $filepath );
        }
        
        $data = stat( $filepath );
        
        $str = basename( $filepath ) . $data[ 'size' ] . $data[ 'mtime' ] . $data[ 'mode' ];
        
        return hash( 'crc32', $str );
    }
    
}