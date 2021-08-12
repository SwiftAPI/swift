<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem;


use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\UnixVisibility\VisibilityConverter;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use League\MimeTypeDetection\MimeTypeDetector;

class LocalFileAdapter extends LocalFilesystemAdapter {

    protected PathPrefixer $prefixer;


    public function __construct(
        protected string $location,
        protected ?VisibilityConverter $visibility = null,
        protected int $writeFlags = LOCK_EX,
        protected int $linkHandling = self::DISALLOW_LINKS,
        protected ?MimeTypeDetector $mimeTypeDetector = null
    ) {
        $this->prefixer     = new PathPrefixer( $location, DIRECTORY_SEPARATOR );
        $this->writeFlags   = $writeFlags;
        $this->linkHandling = $linkHandling;
        $this->visibility   = $visibility ?: new PortableVisibilityConverter();
        $this->ensureDirectoryExists( $location, $this->visibility->defaultForDirectories() );
        $this->mimeTypeDetector = $mimeTypeDetector ?: new FinfoMimeTypeDetector();

        parent::__construct( $location, $visibility, $writeFlags, $linkHandling, $mimeTypeDetector );
    }

    public function exists( string $location ): bool {
        $location = $this->prefixer->prefixPath( $location );

        return is_file( $location ) || is_dir( $location ) || is_link( $location );
    }

    public function dirExists( string $location ): bool {
        $location = $this->prefixer->prefixPath( $location );

        return is_dir( $location );
    }

}