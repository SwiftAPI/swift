<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation;

use Swift\DependencyInjection\Attributes\DI;
use Swift\HttpFoundation\File\UploadedFile;

/**
 * FileBag is a container for uploaded files.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
#[DI( exclude: true, autowire: false )]
class FileBag extends ParameterBag {

    private static array $fileKeys = [ 'error', 'name', 'size', 'tmp_name', 'type' ];

    /**
     * @param array|UploadedFile[] $parameters An array of HTTP files
     */
    public function __construct( array $parameters = [] ) {
        $this->replace( $parameters );
    }

    /**
     * {@inheritdoc}
     */
    public function replace( array $files = [] ) : void{
        $this->parameters = [];
        $this->add( $files );
    }

    /**
     * {@inheritdoc}
     */
    public function add( array $files = [] ): void {
        foreach ( $files as $key => $file ) {
            $this->set( $key, $file );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set( string $key, $value ): void {
        if ( ! \is_array( $value ) && ! $value instanceof UploadedFile ) {
            throw new \InvalidArgumentException( 'An uploaded file must be an array or an instance of UploadedFile.' );
        }

        parent::set( $key, $this->convertFileInformation( $value ) );
    }

    /**
     * Converts uploaded files to UploadedFile instances.
     *
     * @param array|UploadedFile $file A (multi-dimensional) array of uploaded file information
     *
     * @return UploadedFile[]|UploadedFile|null A (multi-dimensional) array of UploadedFile instances
     */
    protected function convertFileInformation( UploadedFile|array $file ): array|UploadedFile|null {
        if ( $file instanceof UploadedFile ) {
            return $file;
        }

        if ( \is_array( $file ) ) {
            $file = $this->fixPhpFilesArray( $file );
            $keys = array_keys( $file );
            sort( $keys );

            if ( $keys == self::$fileKeys ) {
                if ( \UPLOAD_ERR_NO_FILE == $file['error'] ) {
                    $file = null;
                } else {
                    $file = new UploadedFile( $file['tmp_name'], $file['name'], $file['type'], $file['error'], false );
                }
            } else {
                $file = array_map( [ $this, 'convertFileInformation' ], $file );
                if ( array_keys( $keys ) === $keys ) {
                    $file = array_filter( $file );
                }
            }
        }

        return $file;
    }

    /**
     * Fixes a malformed PHP $_FILES array.
     *
     * PHP has a bug that the format of the $_FILES array differs, depending on
     * whether the uploaded file fields had normal field names or array-like
     * field names ("normal" vs. "parent[child]").
     *
     * This method fixes the array to look like the "normal" $_FILES array.
     *
     * It's safe to pass an already converted array, in which case this method
     * just returns the original array unmodified.
     *
     * @param array $data
     *
     * @return array
     */
    protected function fixPhpFilesArray( array $data ): array {
        $keys = array_keys( $data );
        sort( $keys );

        if ( self::$fileKeys != $keys || ! isset( $data['name'] ) || ! \is_array( $data['name'] ) ) {
            return $data;
        }

        $files = $data;
        foreach ( self::$fileKeys as $k ) {
            unset( $files[ $k ] );
        }

        foreach ( $data['name'] as $key => $name ) {
            $files[ $key ] = $this->fixPhpFilesArray( [
                'error'    => $data['error'][ $key ],
                'name'     => $name,
                'type'     => $data['type'][ $key ],
                'tmp_name' => $data['tmp_name'][ $key ],
                'size'     => $data['size'][ $key ],
            ] );
        }

        return $files;
    }
}
