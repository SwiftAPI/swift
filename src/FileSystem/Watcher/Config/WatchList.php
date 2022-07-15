<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem\Watcher\Config;


use Swift\Serializer\Json;

final class WatchList {
    
    private const DEFAULT_EXTENSIONS = [ 'php' ];
    
    /**
     * @var string[]
     */
    private array $paths;
    
    /**
     * @var string[]
     */
    private array $extensions;
    
    /**
     * @var string[]
     */
    private array $ignore;
    
    public function __construct( array $paths = [], array $extensions = [], array $ignore = [] ) {
        $this->paths      = empty( $paths ) ? [ getcwd() ] : $paths;
        $this->extensions = empty( $extensions ) ? self::DEFAULT_EXTENSIONS : $extensions;
        $this->ignore     = $ignore;
    }
    
    public function fileExtensions(): array {
        return array_map(
            static function ( $extension ): string {
                return '*.' . $extension;
            },
            $this->extensions
        );
    }
    
    /**
     * @return string[]
     */
    public function paths(): array {
        return $this->paths;
    }
    
    public function isWatchingForEverything(): bool {
        return empty( $this->paths );
    }
    
    public function hasIgnoring(): bool {
        return ! empty( $this->ignore );
    }
    
    public function ignore(): array {
        return $this->ignore;
    }
    
    public static function fromJson( string $json ): self {
        $values = json_decode( $json, true );
        
        return new self( $values[ 'paths' ], $values[ 'extensions' ], $values[ 'ignore' ] );
    }
    
    public function merge( self $another ): self {
        return new self(
            $this->hasDefaultPath() && ! empty( $another->paths ) ? $another->paths : $this->paths,
            $this->hasDefaultExtensions() && ! empty( $another->extensions ) ? $another->extensions : $this->extensions,
            empty( $this->ignore ) && ! empty( $another->ignore ) ? $another->ignore : $this->ignore
        );
    }
    
    private function hasDefaultPath(): bool {
        return $this->paths === [ getcwd() ];
    }
    
    private function hasDefaultExtensions(): bool {
        return $this->extensions === self::DEFAULT_EXTENSIONS;
    }
    
    public function toJson(): string {
        return (new Json([]))->serialize(
            [
                'paths'      => $this->paths,
                'ignore'     => $this->ignore,
                'extensions' => $this->extensions,
            ]
        );
    }
    
}