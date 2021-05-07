<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\File;

use Swift\HttpFoundation\File\Exception\FileException;
use Swift\HttpFoundation\File\Exception\FileNotFoundException;
use Swift\Kernel\Attributes\DI;
use Symfony\Component\Mime\MimeTypes;

/**
 * A file in the file system.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
#[DI( autowire: false )]
class File extends \SplFileInfo {

    /**
     * Constructs a new file from the given path.
     *
     * @param string $path The path to the file
     * @param bool $checkPath Whether to check the path or not
     *
     * @throws FileNotFoundException If the given path is not a file
     */
    public function __construct( string $path, bool $checkPath = true ) {
        if ( $checkPath && ! is_file( $path ) ) {
            throw new FileNotFoundException( $path );
        }

        parent::__construct( $path );
    }

    /**
     * Returns the extension based on the mime type.
     *
     * If the mime type is unknown, returns null.
     *
     * This method uses the mime type as guessed by getMimeType()
     * to guess the file extension.
     *
     * @return string|null The guessed extension or null if it cannot be guessed
     *
     * @see MimeTypes
     * @see getMimeType()
     */
    public function guessExtension(): ?string {
        if ( ! class_exists( MimeTypes::class ) ) {
            throw new \LogicException( 'You cannot guess the extension as the Mime component is not installed. Try running "composer require symfony/mime".' );
        }

        return MimeTypes::getDefault()->getExtensions( $this->getMimeType() )[0] ?? null;
    }

    /**
     * Returns the mime type of the file.
     *
     * The mime type is guessed using a MimeTypeGuesserInterface instance,
     * which uses finfo_file() then the "file" system binary,
     * depending on which of those are available.
     *
     * @return string|null The guessed mime type (e.g. "application/pdf")
     *
     * @see MimeTypes
     */
    public function getMimeType(): ?string {
        if ( ! class_exists( MimeTypes::class ) ) {
            throw new \LogicException( 'You cannot guess the mime type as the Mime component is not installed. Try running "composer require symfony/mime".' );
        }

        return MimeTypes::getDefault()->guessMimeType( $this->getPathname() );
    }

    /**
     * Moves the file to a new location.
     *
     * @param string $directory
     * @param string|null $name
     *
     * @return self A File object representing the new file
     *
     */
    public function move( string $directory, string $name = null ): File {
        $target = $this->getTargetFile( $directory, $name );

        set_error_handler( static function ( $type, $msg ) use ( &$error ) {
            $error = $msg;
        } );
        $renamed = rename( $this->getPathname(), $target );
        restore_error_handler();
        if ( ! $renamed ) {
            throw new FileException( sprintf( 'Could not move the file "%s" to "%s" (%s).', $this->getPathname(), $target, strip_tags( $error ) ) );
        }

        @chmod( $target, 0666 & ~umask() );

        return $target;
    }

    /**
     * @param string $directory
     * @param string|null $name
     *
     * @return self
     */
    protected function getTargetFile( string $directory, string $name = null ): static {
        if ( ! is_dir( $directory ) ) {
            if ( false === @mkdir( $directory, 0777, true ) && ! is_dir( $directory ) ) {
                throw new FileException( sprintf( 'Unable to create the "%s" directory.', $directory ) );
            }
        } elseif ( ! is_writable( $directory ) ) {
            throw new FileException( sprintf( 'Unable to write in the "%s" directory.', $directory ) );
        }

        $target = rtrim( $directory, '/\\' ) . \DIRECTORY_SEPARATOR . ( null === $name ? $this->getBasename() : $this->getName( $name ) );

        return new self( $target, false );
    }

    /**
     * Returns locale independent base name of the given path.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getName( string $name ): string {
        $originalName = str_replace( '\\', '/', $name );
        $pos          = strrpos( $originalName, '/' );
        $originalName = false === $pos ? $originalName : substr( $originalName, $pos + 1 );

        return $originalName;
    }

    public function getContent(): string {
        $content = file_get_contents( $this->getPathname() );

        if ( false === $content ) {
            throw new FileException( sprintf( 'Could not get the content of the file "%s".', $this->getPathname() ) );
        }

        return $content;
    }
}
