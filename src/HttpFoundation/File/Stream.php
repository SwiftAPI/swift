<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\File;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

/**
 * A PHP stream of unknown size.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class Stream extends File implements StreamInterface {

    /** @var resource|null A resource reference */
    private $stream;

    /** @var bool */
    private bool $seekable;

    /** @var bool */
    private bool $readable;

    /** @var bool */
    private bool $writable;

    /** @var array|mixed|void|null */
    private $uri;

    /** @var int|null */
    private ?int $size;

    /** @var array Hash of readable and writable stream types */
    private const READ_WRITE_HASH = [
        'read' => [
            'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
            'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
            'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a+' => true,
        ],
        'write' => [
            'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
            'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
            'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true,
        ],
    ];

    /**
     * Creates a new PSR-7 stream.
     *
     * @param string|resource|StreamInterface $body
     *
     * @throws InvalidArgumentException
     */
    public static function create( $body = '' ): StreamInterface {
        if ( $body instanceof StreamInterface ) {
            return $body;
        }

        if ( \is_string( $body ) ) {
            $resource = \fopen( 'php://temp', 'rw+' );
            \fwrite( $resource, $body );
            $body = $resource;
        }

        if ( \is_resource( $body ) ) {
            $new           = new self();
            $new->stream   = $body;
            $meta          = \stream_get_meta_data( $new->stream );
            $new->seekable = $meta['seekable'] && 0 === \fseek( $new->stream, 0, \SEEK_CUR );
            $new->readable = isset( self::READ_WRITE_HASH['read'][ $meta['mode'] ] );
            $new->writable = isset( self::READ_WRITE_HASH['write'][ $meta['mode'] ] );
            $new->uri      = $new->getMetadata( 'uri' );

            return $new;
        }

        throw new InvalidArgumentException( 'First argument to Stream::create() must be a string, resource or StreamInterface.' );
    }

    public function getMetadata( $key = null ) {
        if ( ! isset( $this->stream ) ) {
            return $key ? null : [];
        }

        $meta = \stream_get_meta_data( $this->stream );

        if ( null === $key ) {
            return $meta;
        }

        return $meta[ $key ] ?? null;
    }

    /**
     * Closes the stream when the destructed.
     */
    public function __destruct() {
        $this->close();
    }

    public function close(): void {
        if ( isset( $this->stream ) ) {
            if ( \is_resource( $this->stream ) ) {
                \fclose( $this->stream );
            }
            $this->detach();
        }
    }

    public function detach() {
        if ( ! isset( $this->stream ) ) {
            return null;
        }

        $result = $this->stream;
        unset( $this->stream );
        $this->size     = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;

        return $result;
    }

    /**
     * @return string
     */
    public function __toString(): string {
        try {
            if ( $this->isSeekable() ) {
                $this->seek( 0 );
            }

            return $this->getContents();
        } catch ( \Throwable $e ) {
            if ( \PHP_VERSION_ID >= 70400 ) {
                throw $e;
            }

            \restore_error_handler();

            return '';
        }
    }

    public function isSeekable(): bool {
        return $this->seekable;
    }

    public function seek( $offset, $whence = \SEEK_SET ): void {
        if ( ! $this->seekable ) {
            throw new \RuntimeException( 'Stream is not seekable' );
        }

        if ( - 1 === \fseek( $this->stream, $offset, $whence ) ) {
            throw new \RuntimeException( 'Unable to seek to stream position ' . $offset . ' with whence ' . \var_export( $whence, true ) );
        }
    }

    public function getContents(): string {
        if ( ! isset( $this->stream ) ) {
            throw new \RuntimeException( 'Unable to read stream contents' );
        }

        if ( false === $contents = \stream_get_contents( $this->stream ) ) {
            throw new \RuntimeException( 'Unable to read stream contents' );
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): int|false {
        if ( null !== $this->size ) {
            return $this->size;
        }

        if ( ! isset( $this->stream ) ) {
            return false;
        }

        // Clear the stat cache if the stream has a URI
        if ( $this->uri ) {
            \clearstatcache( true, $this->uri );
        }

        $stats = \fstat( $this->stream );
        if ( isset( $stats['size'] ) ) {
            $this->size = $stats['size'];

            return $this->size;
        }

        return false;
    }

    public function tell(): int {
        if ( false === $result = \ftell( $this->stream ) ) {
            throw new \RuntimeException( 'Unable to determine stream position' );
        }

        return $result;
    }

    public function eof(): bool {
        return ! $this->stream || \feof( $this->stream );
    }

    public function rewind(): void {
        $this->seek( 0 );
    }

    public function isWritable(): bool {
        return $this->writable;
    }

    public function write( $string ): int {
        if ( ! $this->writable ) {
            throw new \RuntimeException( 'Cannot write to a non-writable stream' );
        }

        // We can't know the size after writing anything
        $this->size = null;

        if ( false === $result = \fwrite( $this->stream, $string ) ) {
            throw new \RuntimeException( 'Unable to write to stream' );
        }

        return $result;
    }

    public function isReadable(): bool {
        return $this->readable;
    }

    public function read( $length ): string {
        if ( ! $this->readable ) {
            throw new \RuntimeException( 'Cannot read from non-readable stream' );
        }

        if ( false === $result = \fread( $this->stream, $length ) ) {
            throw new \RuntimeException( 'Unable to read from stream' );
        }

        return $result;
    }
}
