<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem\Watcher\Exceptions;


final class InvalidConfigFileContentsException extends \RuntimeException {
    
    public static function invalidContents( string $path ): self {
        return new self( "The content of configfile `{$path}` is not valid. Make sure this file contains valid yaml." );
    }
    
}