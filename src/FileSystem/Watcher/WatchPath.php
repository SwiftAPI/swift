<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem\Watcher;

use Swift\DependencyInjection\Attributes\DI;

#[DI( autowire: false )]
final class WatchPath {
    
    public function __construct(
        private readonly string $pattern,
    ) {
    }
    
    public function isFileOrPattern(): bool {
        return ! $this->isDirectory() || ! file_exists( $this->pattern );
    }
    
    private function directoryPart(): string {
        return pathinfo( $this->pattern, PATHINFO_DIRNAME );
    }
    
    public function fileName(): string {
        return pathinfo( $this->pattern, PATHINFO_BASENAME );
    }
    
    private function isDirectory(): bool {
        return is_dir( $this->pattern );
    }
    
    public function path(): string {
        return $this->isDirectory() ? $this->pattern : $this->directoryPart();
    }
    
}