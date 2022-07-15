<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem\Watcher\ResourceWatcherBased;


class ResourceCachePhpFile extends ResourceCacheMemory {
    
    protected bool $hasPendingChanges = false;
    
    /**
     * Constructor.
     *
     * @param string $filename The cache ".PHP" file. E.g: "resource-watcher-cache.php"
     */
    public function __construct(
        protected string $filename
    ) {
        $this->warmUpCacheFromFile( $this->filename );
    }
    
    /**
     * @inheritDoc
     */
    public function write( string $filename, string $hash ): void {
        if ( $hash === $this->read( $filename ) ) {
            return;
        }
        
        parent::write( $filename, $hash );
        
        $this->hasPendingChanges = true;
    }
    
    /**
     * @inheritDoc
     */
    public function save(): void {
        if ( $this->hasPendingChanges === false ) {
            return;
        }
        
        $content = $this->composeContentCacheFile( $this->getAll() );
        
        if ( @file_put_contents( $this->filename, $content ) === false ) {
            throw new \RuntimeException( sprintf( 'Failed to write the cache file "%s".', $this->filename ) );
        }
        
        $this->hasPendingChanges = false;
        $this->isInitialized     = true;
    }
    
    /**
     * @param string $filename
     *
     * @return void
     */
    private function warmUpCacheFromFile( $filename ): void {
        if ( preg_match( '#\.php$#', $filename ) == false ) {
            throw new \InvalidArgumentException( 'The cache filename must ends with the extension ".php".' );
        }
        
        if ( file_exists( $filename ) == false ) {
            $this->hasPendingChanges = true;
            
            return;
        }
        
        $fileContent = include( $filename );
        
        if ( ! is_array( $fileContent ) ) {
            throw new \InvalidArgumentException( 'Cache file invalid format.' );
        }
        
        foreach ( $fileContent as $filename => $hash ) {
            $this->write( $filename, $hash );
        }
        
        $this->isInitialized = true;
    }
    
    /**
     * @param array $cacheEntries
     *
     * @return string
     */
    private function composeContentCacheFile( array $cacheEntries ): string {
        $data = '';
        
        foreach ( $cacheEntries as $filename => $hash ) {
            $data .= sprintf( "'%s'=>'%s',", $filename, $hash );
        }
        
        return "<?php\nreturn [$data];";
    }
    
}