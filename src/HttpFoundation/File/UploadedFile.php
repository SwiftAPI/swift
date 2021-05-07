<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\File;

use JetBrains\PhpStorm\Pure;
use Swift\HttpFoundation\File\Exception\CannotWriteFileException;
use Swift\HttpFoundation\File\Exception\ExtensionFileException;
use Swift\HttpFoundation\File\Exception\FileException;
use Swift\HttpFoundation\File\Exception\FormSizeFileException;
use Swift\HttpFoundation\File\Exception\IniSizeFileException;
use Swift\HttpFoundation\File\Exception\NoFileException;
use Swift\HttpFoundation\File\Exception\NoTmpDirFileException;
use Swift\HttpFoundation\File\Exception\PartialFileException;
use Swift\Kernel\Attributes\DI;
use Symfony\Component\Mime\MimeTypes;
use Psr\Http\Message\{StreamInterface, UploadedFileInterface};

/**
 * A file uploaded through a form.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Florian Eckerstorfer <florian@eckerstorfer.org>
 * @author Fabien Potencier <fabien@symfony.com>
 */
#[DI( autowire: false )]
class UploadedFile extends File implements UploadedFileInterface {
    /** @var array */
    private const ERRORS = [
        \UPLOAD_ERR_OK         => 1,
        \UPLOAD_ERR_INI_SIZE   => 1,
        \UPLOAD_ERR_FORM_SIZE  => 1,
        \UPLOAD_ERR_PARTIAL    => 1,
        \UPLOAD_ERR_NO_FILE    => 1,
        \UPLOAD_ERR_NO_TMP_DIR => 1,
        \UPLOAD_ERR_CANT_WRITE => 1,
        \UPLOAD_ERR_EXTENSION  => 1,
    ];

    /** @var string */
    private ?string $clientFilename;

    /** @var string */
    private ?string $clientMediaType;

    /** @var int */
    private int $error;

    /** @var string|null */
    private ?string $file;

    /** @var bool */
    private $moved = false;

    /** @var int */
    private int $size;

    /** @var StreamInterface|null */
    private $stream;
    private $test;

    /**
     * Accepts the information of the uploaded file as provided by the PHP global $_FILES.
     *
     * The file object is only created when the uploaded file is valid (i.e. when the
     * isValid() method returns true). Otherwise the only methods that could be called
     * on an UploadedFile instance are:
     *
     *   * getClientOriginalName,
     *   * getClientMimeType,
     *   * isValid,
     *   * getError.
     *
     * Calling any other method on an non-valid instance will cause an unpredictable result.
     *
     * @param StreamInterface|string|resource $streamOrFile The full temporary path to the file
     * @param string $clientFilename The original file name of the uploaded file
     * @param string|null $clientMediaType The type of the file as provided by PHP; null defaults to application/octet-stream
     * @param int|null $errorStatus
     * @param int|null $size
     * @param bool $test Whether the test mode is active
     *                                  Local files are used in test mode hence the code should not enforce HTTP uploads
     */
    public function __construct( $streamOrFile, string $clientFilename, string $clientMediaType = null, int $errorStatus = null, ?int $size = null, bool $test = false ) {
        if ( false === \is_int( $errorStatus ) || ! isset( self::ERRORS[ $errorStatus ] ) ) {
            throw new \InvalidArgumentException( 'Upload file error status must be an integer value and one of the "UPLOAD_ERR_*" constants.' );
        }

        if ( false === \is_int( $size ) ) {
            throw new \InvalidArgumentException( 'Upload file size must be an integer' );
        }

        if ( null !== $clientFilename && ! \is_string( $clientFilename ) ) {
            throw new \InvalidArgumentException( 'Upload file client filename must be a string or null' );
        }

        if ( null !== $clientMediaType && ! \is_string( $clientMediaType ) ) {
            throw new \InvalidArgumentException( 'Upload file client media type must be a string or null' );
        }

        $this->error           = $errorStatus;
        $this->size            = $size;
        $this->clientFilename  = $clientFilename;
        $this->clientMediaType = $clientMediaType;

        if ( \UPLOAD_ERR_OK === $this->error ) {
            // Depending on the value set file or stream variable.
            if ( \is_string( $streamOrFile ) ) {
                $this->file = $streamOrFile;
            } elseif ( \is_resource( $streamOrFile ) ) {
                $this->stream = Stream::create( $streamOrFile );
            } elseif ( $streamOrFile instanceof StreamInterface ) {
                $this->stream = $streamOrFile;
            } else {
                throw new \InvalidArgumentException( 'Invalid stream or file provided for UploadedFile' );
            }
        }
        $this->test = $test;

        if ( is_string( $streamOrFile ) ) {
            parent::__construct( $streamOrFile, \UPLOAD_ERR_OK === $this->error );
        }
    }

    /**
     * Returns the original file extension.
     *
     * It is extracted from the original file name that was uploaded.
     * Then it should not be considered as a safe value.
     *
     * @return string The extension
     */
    #[Pure] public function getClientOriginalExtension(): string {
        return pathinfo( $this->clientFilename, \PATHINFO_EXTENSION );
    }

    /**
     * Returns the extension based on the client mime type.
     *
     * If the mime type is unknown, returns null.
     *
     * This method uses the mime type as guessed by getClientMimeType()
     * to guess the file extension. As such, the extension returned
     * by this method cannot be trusted.
     *
     * For a trusted extension, use guessExtension() instead (which guesses
     * the extension based on the guessed mime type for the file).
     *
     * @return string|null The guessed extension or null if it cannot be guessed
     *
     * @see guessExtension()
     * @see getClientMimeType()
     */
    public function guessClientExtension(): ?string {
        if ( ! class_exists( MimeTypes::class ) ) {
            throw new \LogicException( 'You cannot guess the extension as the Mime component is not installed. Try running "composer require symfony/mime".' );
        }

        return MimeTypes::getDefault()->getExtensions( $this->getClientMimeType() )[0] ?? null;
    }

    /**
     * Returns the file mime type.
     *
     * The client mime type is extracted from the request from which the file
     * was uploaded, so it should not be considered as a safe value.
     *
     * For a trusted mime type, use getMimeType() instead (which guesses the mime
     * type based on the file content).
     *
     * @return string The mime type
     *
     * @see getMimeType()
     */
    public function getClientMimeType(): string {
        return $this->clientMediaType;
    }

    /**
     * Returns the upload error.
     *
     * If the upload was successful, the constant UPLOAD_ERR_OK is returned.
     * Otherwise one of the other UPLOAD_ERR_XXX constants is returned.
     *
     * @return int The upload error
     */
    public function getError(): int {
        return $this->error;
    }

    /**
     * Moves the file to a new location.
     *
     * @param string $directory
     * @param string|null $name
     *
     * @return File A File object representing the new file
     *
     */
    public function move( string $directory, string $name = null ): File {
        $this->moveTo($directory . '/' . $name);
    }

    /**
     * Returns whether the file was uploaded successfully.
     *
     * @return bool True if the file has been uploaded with HTTP and no error occurred
     */
    public function isValid(): bool {
        $isOk = \UPLOAD_ERR_OK === $this->error;

        return $this->test ? $isOk : $isOk && is_uploaded_file( $this->getPathname() );
    }

    /**
     * Returns an informative upload error message.
     *
     * @return string The error message regarding the specified error code
     */
    public function getErrorMessage(): string {
        static $errors = [
            \UPLOAD_ERR_INI_SIZE   => 'The file "%s" exceeds your upload_max_filesize ini directive (limit is %d KiB).',
            \UPLOAD_ERR_FORM_SIZE  => 'The file "%s" exceeds the upload limit defined in your form.',
            \UPLOAD_ERR_PARTIAL    => 'The file "%s" was only partially uploaded.',
            \UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            \UPLOAD_ERR_CANT_WRITE => 'The file "%s" could not be written on disk.',
            \UPLOAD_ERR_NO_TMP_DIR => 'File could not be uploaded: missing temporary directory.',
            \UPLOAD_ERR_EXTENSION  => 'File upload was stopped by a PHP extension.',
        ];

        $errorCode   = $this->error;
        $maxFilesize = \UPLOAD_ERR_INI_SIZE === $errorCode ? self::getMaxFilesize() / 1024 : 0;
        $message     = isset( $errors[ $errorCode ] ) ? $errors[ $errorCode ] : 'The file "%s" was not uploaded due to an unknown error.';

        return sprintf( $message, $this->getClientOriginalName(), $maxFilesize );
    }

    /**
     * Returns the maximum size of an uploaded file as configured in php.ini.
     *
     * @return int The maximum size of an uploaded file in bytes
     */
    public static function getMaxFilesize(): int {
        $sizePostMax   = self::parseFilesize( ini_get( 'post_max_size' ) );
        $sizeUploadMax = self::parseFilesize( ini_get( 'upload_max_filesize' ) );

        return min( $sizePostMax ?: \PHP_INT_MAX, $sizeUploadMax ?: \PHP_INT_MAX );
    }

    /**
     * Returns the given size from an ini value in bytes.
     *
     * @param $size
     *
     * @return int
     */
    private static function parseFilesize( $size ): int {
        if ( '' === $size ) {
            return 0;
        }

        $size = strtolower( $size );

        $max = ltrim( $size, '+' );
        if ( str_starts_with( $max, '0x' ) ) {
            $max = \intval( $max, 16 );
        } elseif ( str_starts_with( $max, '0' ) ) {
            $max = \intval( $max, 8 );
        } else {
            $max = (int) $max;
        }

        switch ( substr( $size, - 1 ) ) {
            case 't':
                $max *= 1024;
            // no break
            case 'g':
                $max *= 1024;
            // no break
            case 'm':
                $max *= 1024;
            // no break
            case 'k':
                $max *= 1024;
        }

        return $max;
    }

    /**
     * Returns the original file name.
     *
     * It is extracted from the request from which the file has been uploaded.
     * Then it should not be considered as a safe value.
     *
     * @return string The original name
     */
    public function getClientOriginalName(): string {
        return $this->clientFilename;
    }

    /**
     * @throws \RuntimeException if is moved or not ok
     */
    private function validateActive(): void
    {
        if (\UPLOAD_ERR_OK !== $this->error) {
            throw new \RuntimeException('Cannot retrieve stream due to upload error');
        }

        if ($this->moved) {
            throw new \RuntimeException('Cannot retrieve stream after it has already been moved');
        }
    }

    public function getStream(): StreamInterface
    {
        $this->validateActive();

        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        }

        $resource = \fopen($this->file, 'r');

        return Stream::create($resource);
    }

    public function moveTo($targetPath): void
    {
        $this->validateActive();

        if (!\is_string($targetPath) || '' === $targetPath) {
            throw new \InvalidArgumentException('Invalid path provided for move operation; must be a non-empty string');
        }

        if (null !== $this->file) {
            if ( !$this->isValid() ) {
                switch ( $this->error ) {
                    case \UPLOAD_ERR_INI_SIZE:
                        throw new IniSizeFileException( $this->getErrorMessage() );
                    case \UPLOAD_ERR_FORM_SIZE:
                        throw new FormSizeFileException( $this->getErrorMessage() );
                    case \UPLOAD_ERR_PARTIAL:
                        throw new PartialFileException( $this->getErrorMessage() );
                    case \UPLOAD_ERR_NO_FILE:
                        throw new NoFileException( $this->getErrorMessage() );
                    case \UPLOAD_ERR_CANT_WRITE:
                        throw new CannotWriteFileException( $this->getErrorMessage() );
                    case \UPLOAD_ERR_NO_TMP_DIR:
                        throw new NoTmpDirFileException( $this->getErrorMessage() );
                    case \UPLOAD_ERR_EXTENSION:
                        throw new ExtensionFileException( $this->getErrorMessage() );
                }

                throw new FileException( $this->getErrorMessage() );
            }

            $fileInfo = pathinfo($targetPath);
            $directory = $fileInfo['dirname'];
            $name = $fileInfo['basename'];
            if ( $this->test ) {
                parent::move( $directory, $name );
            }

            $target = $this->getTargetFile( $directory, $name );

            set_error_handler( static function ( $type, $msg ) use ( &$error ) {
                $error = $msg;
            } );
            $this->moved = 'cli' === \PHP_SAPI ? \rename($this->file, $targetPath) : move_uploaded_file( $this->getPathname(), $target );
            restore_error_handler();

            @chmod( $target, 0666 & ~umask() );
        } else {
            $stream = $this->getStream();
            if ($stream->isSeekable()) {
                $stream->rewind();
            }

            // Copy the contents of a stream into another stream until end-of-file.
            $dest = Stream::create(\fopen($targetPath, 'w'));
            while (!$stream->eof()) {
                if (!$dest->write($stream->read(1048576))) {
                    break;
                }
            }

            $this->moved = true;
        }

        if (false === $this->moved) {
            throw new \RuntimeException(\sprintf('Uploaded file could not be moved to %s', $targetPath));
        }
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }
}
