<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Utils;


/**
 * Class ClassNameMapper
 * @package Swift\GraphQl2
 */
class ClassNameMapper extends \Mouf\Composer\ClassNameMapper {
    
    /**
     *
     * @var array
     */
    private array $psr0Namespaces = [];
    
    /**
     *
     * @var array
     */
    private array $psr4Namespaces = [];
    
    /**
     * ClassNameMapper constructor.
     */
    public function __construct() {
        $this->registerPsr4Namespace( 'App\\', INCLUDE_DIR . '/app' );
        $this->registerPsr4Namespace( 'Swift\\', INCLUDE_DIR . '/src/swift' );
        $this->registerPsr4Namespace( 'Swift\\', INCLUDE_DIR . '/vendor/swift/src' );
    }
    
    /**
     * Returns a list of paths that can be used to store $className.
     *
     * @param string $className
     *
     * @return string[]
     */
    public function getPossibleFileNames( $className ): array {
        $possibleFileNames = [];
        $className         = ltrim( $className, '\\' );
        
        $psr0unfactorizedAutoload = self::unfactorizeAutoload( $this->psr0Namespaces );
        
        foreach ( $psr0unfactorizedAutoload as $result ) {
            $namespace = $result[ 'namespace' ];
            $directory = $result[ 'directory' ];
            
            if ( $namespace === '' ) {
                $tmpClassName = $className;
                if ( $lastNsPos = strrpos( $tmpClassName, '\\' ) ) {
                    $namespace    = substr( $tmpClassName, 0, $lastNsPos );
                    $tmpClassName = substr( $tmpClassName, $lastNsPos + 1 );
                }
                
                $fileName            = str_replace( '\\', '/', $namespace ) . '/' . str_replace( '_', '/', $tmpClassName ) . '.php';
                $possibleFileNames[] = $directory . $fileName;
            } else if ( str_starts_with( $className, $namespace ) ) {
                $tmpClassName = $className;
                $fileName     = '';
                if ( $lastNsPos = strrpos( $tmpClassName, '\\' ) ) {
                    $namespace    = substr( $tmpClassName, 0, $lastNsPos );
                    $tmpClassName = substr( $tmpClassName, $lastNsPos + 1 );
                    $fileName     = str_replace( '\\', '/', $namespace ) . '/';
                }
                $fileName .= str_replace( '_', '/', $tmpClassName ) . '.php';
                
                $possibleFileNames[] = $directory . $fileName;
            }
        }
        
        $psr4unfactorizedAutoload = self::unfactorizeAutoload( $this->psr4Namespaces );
        
        foreach ( $psr4unfactorizedAutoload as $result ) {
            $namespace = $result[ 'namespace' ];
            $directory = $result[ 'directory' ];
            
            if ( $namespace === '' ) {
                $fileName            = str_replace( '\\', '/', $className ) . '.php';
                $possibleFileNames[] = $directory . $fileName;
            } else if ( str_starts_with( $className, $namespace ) ) {
                $shortenedClassName = substr( $className, strlen( $namespace ) );
                
                if ( $lastNsPos = strrpos( $shortenedClassName, '\\' ) ) {
                    $namespace          = substr( $shortenedClassName, 0, $lastNsPos );
                    $shortenedClassName = substr( $shortenedClassName, $lastNsPos + 1 );
                    $fileName           = str_replace( '\\', '/', $namespace ) . '/' . $shortenedClassName;
                } else {
                    $fileName = $shortenedClassName;
                }
                $fileName .= '.php';
                
                $possibleFileNames[] = $directory . $fileName;
            }
        }
        
        
        return $possibleFileNames;
    }
    
    /**
     * Takes in parameter an array like
     * [{ "Mouf": "src/" }] or [{ "Mouf": ["src/", "src2/"] }] .
     * returns
     * [
     *    {"namespace"=> "Mouf", "directory"=>"src/"},
     *    {"namespace"=> "Mouf", "directory"=>"src2/"}
     * ]
     *
     * @param array $autoload
     *
     * @return array<int, array<string, string>>
     */
    private static function unfactorizeAutoload( array $autoload ): array {
        $result = [];
        foreach ( $autoload as $namespace => $directories ) {
            if ( ! is_array( $directories ) ) {
                $result[] = [
                    "namespace" => $namespace,
                    "directory" => self::normalizeDirectory( $directories ),
                ];
            } else {
                foreach ( $directories as $dir ) {
                    $result[] = [
                        "namespace" => $namespace,
                        "directory" => self::normalizeDirectory( $dir ),
                    ];
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Makes sure the directory ends with a / (unless the string is empty)
     *
     * @param string $dir
     *
     * @return string
     */
    private static function normalizeDirectory( string $dir ): string {
        return $dir === '' ? '' : rtrim( $dir, '\\/' ) . '/';
    }
    
    /**
     * Registers a PSR-4 namespace.
     *
     * @param string       $namespace The namespace to register
     * @param string|array $path      The path on the filesystem (or an array of paths)
     */
    public function registerPsr4Namespace( $namespace, $path ): void {
        // A namespace always ends with a \
        $namespace = trim( $namespace, '\\' ) . '\\';
        if ( $namespace === '\\' ) {
            $namespace = '';
        }
        
        if ( ! is_array( $path ) ) {
            $path = [ $path ];
        }
        // Paths always end with a /
        $paths = array_map( [ self::class, 'normalizeDirectory' ], $path );
        
        if ( ! isset( $this->psr4Namespaces[ $namespace ] ) ) {
            $this->psr4Namespaces[ $namespace ] = $paths;
        } else {
            $this->psr4Namespaces[ $namespace ] = array_merge( $this->psr4Namespaces[ $namespace ], $paths );
        }
    }
    
}