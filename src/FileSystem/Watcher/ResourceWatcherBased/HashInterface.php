<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem\Watcher\ResourceWatcherBased;


interface HashInterface {
    
    /**
     * Calculates the hash of a file.
     *
     * @param string $filepath
     *
     * @return string Returns a string containing the calculated message digest.
     */
    public function hash( string $filepath ): string;
    
}