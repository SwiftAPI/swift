<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation;

use JetBrains\PhpStorm\Pure;
use Swift\DependencyInjection\Attributes\DI;
use Swift\HttpFoundation\File\Exception\FileException;
use Swift\HttpFoundation\File\File;

/**
 * BinaryFileResponse represents an HTTP response delivering a file.
 *
 * @author Niklas Fiekas <niklas.fiekas@tu-clausthal.de>
 * @author stealth35 <stealth35-php@live.fr>
 * @author Igor Wiedler <igor@wiedler.ch>
 * @author Jordan Alliot <jordan.alliot@gmail.com>
 * @author Sergey Linnik <linniksa@gmail.com>
 */
#[DI( exclude: true, autowire: false )]
class BinaryFileResponse extends Response {
    protected static $trustXSendfileTypeHeader = false;

    /**
     * @var File
     */
    protected $file;
    protected $offset = 0;
    protected $maxlen = - 1;
    protected $deleteFileAfterSend = false;

    /**
     * @param \SplFileInfo|string $file The file to stream
     * @param int $status The response status code
     * @param array $headers An array of response headers
     * @param bool $public Files are public by default
     * @param string|null $contentDisposition The type of Content-Disposition to set automatically with the filename
     * @param bool $autoEtag Whether the ETag header should be automatically set
     * @param bool $autoLastModified Whether the Last-Modified header should be automatically set
     */
    public function __construct( $file, int $status = 200, array $headers = [], bool $public = true, string $contentDisposition = null, bool $autoEtag = false, bool $autoLastModified = true ) {
        parent::__construct( $file, $status, $headers );

        $this->setFile( $file, $contentDisposition, $autoEtag, $autoLastModified );

        if ( $public ) {
            $this->headers->addCacheControlDirective( 'public' );
            $this->headers->removeCacheControlDirective( 'private' );
        }
    }

    /**
     * Trust X-Sendfile-Type header.
     */
    public static function trustXSendfileTypeHeader(): void {
        self::$trustXSendfileTypeHeader = true;
    }

    /**
     * Gets the file.
     *
     * @return File The file to stream
     */
    public function getFile(): File {
        return $this->file;
    }

    /**
     * Sets the file to stream.
     *
     * @param \SplFileInfo|string $file The file to stream
     * @param string|null $contentDisposition
     * @param bool $autoEtag
     * @param bool $autoLastModified
     *
     * @return $this
     *
     */
    private function setFile( $file, string $contentDisposition = null, bool $autoEtag = false, bool $autoLastModified = true ): static {
        if ( ! $file instanceof File ) {
            if ( $file instanceof \SplFileInfo ) {
                $file = new File( $file->getPathname() );
            } else {
                $file = new File( (string) $file );
            }
        }

        if ( ! $file->isReadable() ) {
            throw new FileException( 'File must be readable.' );
        }

        $this->file = $file;

        if ( $autoEtag ) {
            $etag = $this->makeETag($this->file->getPathname());
            if ( null === $etag ) {
                $this->headers->remove( 'Etag' );
            } else {
                if ( ! str_starts_with( $etag, '"' ) ) {
                    $etag = '"' . $etag . '"';
                }

                $this->headers->set( 'ETag', ( true === false ? 'W/' : '' ) . $etag );
            }
        }

        if ( $autoLastModified ) {
            $this->headers->set( 'Last-Modified', $this->autoLastModified()?->format( 'D, d M Y H:i:s' ) . ' GMT' );
        }

        if ( $contentDisposition ) {
            $this->headers->set( 'Content-Disposition', $this->getContentDispositionHeader($contentDisposition) );
        }

        return $this;
    }

    /**
     * Automatically sets the Last-Modified header according the file modification date.
     */
    public function withAutoLastModified(): static {
        return $this->withLastModified($this->autoLastModified());
    }

    private function autoLastModified(): \DateTime|null {
        $modified = \DateTime::createFromFormat( 'U', $this->file->getMTime());
        return $modified ?? null;
    }

    /**
     * Automatically sets the ETag header according to the checksum of the file.
     */
    public function withAutoEtag(): static {
        $new = clone $this;

        return $this->withEtag($this->makeETag($new->file->getPathname()));
    }

    #[Pure] public function makeETag( string $pathname ): string {
        return base64_encode( hash_file( 'sha256', $pathname, true ) );
    }

    /**
     * Sets the Content-Disposition header with the given filename.
     *
     * @param string $disposition ResponseHeaderBag::DISPOSITION_INLINE or ResponseHeaderBag::DISPOSITION_ATTACHMENT
     * @param string $filename Optionally use this UTF-8 encoded filename instead of the real name of the file
     * @param string $filenameFallback A fallback filename, containing only ASCII characters. Defaults to an automatically encoded filename
     *
     * @return static
     */
    public function withContentDisposition( string $disposition, string $filename = '', string $filenameFallback = '' ): static {
        $new = clone $this;

        $new->headers->set( 'Content-Disposition', $this->getContentDispositionHeader($disposition, $filename, $filenameFallback) );

        return $new;
    }

    /**
     * Creates the Content-Disposition header with the given filename.
     *
     * @param string $disposition ResponseHeaderBag::DISPOSITION_INLINE or ResponseHeaderBag::DISPOSITION_ATTACHMENT
     * @param string $filename Optionally use this UTF-8 encoded filename instead of the real name of the file
     * @param string $filenameFallback A fallback filename, containing only ASCII characters. Defaults to an automatically encoded filename
     *
     * @return static
     */
    public function getContentDispositionHeader( string $disposition, string $filename = '', string $filenameFallback = '' ): string {
        if ( '' === $filename ) {
            $filename = $this->file->getFilename();
        }

        if ( '' === $filenameFallback && ( ! preg_match( '/^[\x20-\x7e]*$/', $filename ) || str_contains( $filename, '%' ) ) ) {
            $encoding = mb_detect_encoding( $filename, null, true ) ?: '8bit';

            for ( $i = 0, $filenameLength = mb_strlen( $filename, $encoding ); $i < $filenameLength; ++ $i ) {
                $char = mb_substr( $filename, $i, 1, $encoding );

                if ( '%' === $char || \ord( $char ) < 32 || \ord( $char ) > 126 ) {
                    $filenameFallback .= '_';
                } else {
                    $filenameFallback .= $char;
                }
            }
        }

        return $this->headers->makeDisposition( $disposition, $filename, $filenameFallback );
    }

    /**
     * {@inheritdoc}
     */
    public function prepare( Request $request ): static {
        $new = clone $this;
        if ( ! $new->headers->has( 'Content-Type' ) ) {
            $new->headers->set( 'Content-Type', $new->file->getMimeType() ?: 'application/octet-stream' );
        }

        if ( 'HTTP/1.0' !== $request->server->get( 'SERVER_PROTOCOL' ) ) {
            $new = $this->withProtocolVersion('1.1');
        }

        $new = $new->withEnsureIEOverSSLCompatibility( $request );

        $new->offset = 0;
        $new->maxlen = - 1;

        if ( false === $fileSize = $new->file->getSize() ) {
            return $new;
        }
        $new->headers->set( 'Content-Length', $fileSize );

        if ( ! $new->headers->has( 'Accept-Ranges' ) ) {
            // Only accept ranges on safe HTTP methods
            $new->headers->set( 'Accept-Ranges', $request->isMethodSafe() ? 'bytes' : 'none' );
        }

        if ( self::$trustXSendfileTypeHeader && $request->headers->has( 'X-Sendfile-Type' ) ) {
            // Use X-Sendfile, do not send any content.
            $type = $request->headers->get( 'X-Sendfile-Type' );
            $path = $new->file->getRealPath();
            // Fall back to scheme://path for stream wrapped locations.
            if ( false === $path ) {
                $path = $new->file->getPathname();
            }
            if ( 'x-accel-redirect' === strtolower( $type ) ) {
                // Do X-Accel-Mapping substitutions.
                // @link https://www.nginx.com/resources/wiki/start/topics/examples/x-accel/#x-accel-redirect
                $parts = HeaderUtils::split( $request->headers->get( 'X-Accel-Mapping', '' ), ',=' );
                foreach ( $parts as $part ) {
                    [ $pathPrefix, $location ] = $part;
                    if ( str_starts_with( $path, $pathPrefix ) ) {
                        $path = $location . substr( $path, \strlen( $pathPrefix ) );
                        // Only set X-Accel-Redirect header if a valid URI can be produced
                        // as nginx does not serve arbitrary file paths.
                        $new->headers->set( $type, $path );
                        $new->maxlen = 0;
                        break;
                    }
                }
            } else {
                $this->headers->set( $type, $path );
                $this->maxlen = 0;
            }
        } elseif ( $request->headers->has( 'Range' ) && $request->isMethod( 'GET' ) ) {
            // Process the range headers.
            if ( ! $request->headers->has( 'If-Range' ) || $new->hasValidIfRangeHeader( $request->headers->get( 'If-Range' ) ) ) {
                $range = $request->headers->get( 'Range' );

                if ( str_starts_with( $range, 'bytes=' ) ) {
                    [ $start, $end ] = explode( '-', substr( $range, 6 ), 2 ) + [ 0 ];

                    $end = ( '' === $end ) ? $fileSize - 1 : (int) $end;

                    if ( '' === $start ) {
                        $start = $fileSize - $end;
                        $end   = $fileSize - 1;
                    } else {
                        $start = (int) $start;
                    }

                    if ( $start <= $end ) {
                        $end = min( $end, $fileSize - 1 );
                        if ( $start < 0 || $start > $end ) {
                            $new = $new->withStatus( 416 );
                            $new->headers->set( 'Content-Range', sprintf( 'bytes */%s', $fileSize ) );
                        } elseif ( $end - $start < $fileSize - 1 ) {
                            $new->maxlen = $end < $fileSize ? $end - $start + 1 : - 1;
                            $new->offset = $start;

                            $new = $new->withStatus( 206 );
                            $new->headers->set( 'Content-Range', sprintf( 'bytes %s-%s/%s', $start, $end, $fileSize ) );
                            $new->headers->set( 'Content-Length', $end - $start + 1 );
                        }
                    }
                }
            }
        }

        return $new;
    }

    private function hasValidIfRangeHeader( ?string $header ): bool {
        if ( $this->getEtag() === $header ) {
            return true;
        }

        if ( null === $lastModified = $this->getLastModified() ) {
            return false;
        }

        return $lastModified->format( 'D, d M Y H:i:s' ) . ' GMT' === $header;
    }

    /**
     * Sends the file.
     *
     * {@inheritdoc}
     */
    public function sendContent(): static {

        // @TODO: Decide whether to keep using this or to move towards the common Stream usage

        if ( ! $this->isSuccessful() ) {
            return parent::sendContent();
        }

        if ( 0 === $this->maxlen ) {
            return $this;
        }

        $out  = fopen( 'php://output', 'wb' );
        $file = fopen( $this->file->getPathname(), 'rb' );

        stream_copy_to_stream( $file, $out, $this->maxlen, $this->offset );

        fclose( $out );
        fclose( $file );

        if ( $this->deleteFileAfterSend && is_file( $this->file->getPathname() ) ) {
            unlink( $this->file->getPathname() );
        }

        return $this;
    }

    /**
     * If this is set to true, the file will be unlinked after the request is sent
     * Note: If the X-Sendfile header is used, the deleteFileAfterSend setting will not be used.
     *
     * @param bool $shouldDelete
     *
     * @return static
     */
    public function withDeleteFileAfterSend( bool $shouldDelete = true ): static {
        $new = clone $this;
        $new->deleteFileAfterSend = $shouldDelete;

        return $new;
    }
}
