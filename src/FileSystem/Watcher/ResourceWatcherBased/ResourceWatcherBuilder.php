<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem\Watcher\ResourceWatcherBased;


use Swift\DependencyInjection\Attributes\DI;
use Swift\FileSystem\Watcher\Config\WatchList;
use Swift\FileSystem\Watcher\WatchPath;
use Symfony\Component\Finder\Finder;

#[DI( autowire: false )]
final class ResourceWatcherBuilder {
    
    public static function create( WatchList $watchList ): ResourceWatcher {
        return new ResourceWatcher(
            new ResourceCacheMemory(), self::makeFinder( $watchList ), new Crc32ContentHash()
        );
    }
    
    private static function makeFinder( WatchList $watchList ): Finder {
        $finder       = self::makeDefaultFinder( $watchList );
        $pathsToWatch = self::extractWatchPathsFromList( $watchList );
        
        foreach ( $pathsToWatch as $watchPath ) {
            self::appendFinderWithPath( $finder, $watchPath );
        }
        
        return $finder;
    }
    
    private static function extractWatchPathsFromList( WatchList $watchList ): array {
        return array_map(
            static function ( $path ): WatchPath {
                return new WatchPath( $path );
            }, $watchList->paths()
        );
    }
    
    private static function makeDefaultFinder( WatchList $watchList ): Finder {
        return ( new Finder() )
            ->ignoreDotFiles( false )
            ->ignoreVCS( false )
            ->name( $watchList->fileExtensions() )
            ->files()
            ->notPath( $watchList->ignore() );
    }
    
    private static function appendFinderWithPath( Finder $finder, WatchPath $watchPath ): void {
        $finder->in( $watchPath->path() );
        
        if ( $watchPath->isFileOrPattern() ) {
            $finder->name( $watchPath->fileName() );
        }
    }
    
}