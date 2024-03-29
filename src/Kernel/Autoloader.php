<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel;


class Autoloader {
    
    protected static bool $initialized = false;
    
    protected array $registered = [];
    
    public static function initialize(): void {
        if ( static::$initialized ) {
            return;
        }
        ( new self() )->doInitialize();
        self::$initialized = true;
    }
    
    public function doInitialize(): void {
        spl_autoload_register( function ( $className ) {
            $classPathLc = lcfirst( str_replace( "\\", DIRECTORY_SEPARATOR, $className ) . '.php' );
            $classPathUc = ucfirst( str_replace( "\\", DIRECTORY_SEPARATOR, $className ) . '.php' );
            
            if ( in_array( $classPathLc, $this->registered, true ) || in_array( $classPathUc, $this->registered, true ) ) {
                return;
            }
            
            // Check if class exists
            if ( file_exists( INCLUDE_DIR . '/vendor/' . $classPathLc ) ) {
                include_once INCLUDE_DIR . '/vendor/' . $classPathLc;
                
                return;
            }
            if ( file_exists( INCLUDE_DIR . '/vendor/' . $classPathUc ) ) {
                include_once INCLUDE_DIR . '/vendor/' . $classPathUc;
                
                return;
            }
            if ( file_exists( INCLUDE_DIR . '/src/' . $classPathLc ) ) {
                include_once INCLUDE_DIR . '/src/' . $classPathLc;
                
                return;
            }
            if ( file_exists( INCLUDE_DIR . '/src/' . $classPathUc ) ) {
                include_once INCLUDE_DIR . '/src/' . $classPathUc;
                
                return;
            }
            if ( file_exists( INCLUDE_DIR . '/app/' . $classPathLc ) ) {
                include_once INCLUDE_DIR . '/app/' . $classPathLc;
                
                return;
            }
            if ( file_exists( INCLUDE_DIR . '/app/' . $classPathUc ) ) {
                include_once INCLUDE_DIR . '/app/' . $classPathUc;
                
                return;
            }
            if ( file_exists( INCLUDE_DIR . '/app/' . str_replace( 'app' . DIRECTORY_SEPARATOR, '', $classPathLc ) ) ) {
                include_once INCLUDE_DIR . '/app/' . str_replace( 'app' . DIRECTORY_SEPARATOR, '', $classPathLc );
                
                return;
            }
            if ( file_exists( INCLUDE_DIR . '/app/' . str_replace( 'App' . DIRECTORY_SEPARATOR, '', $classPathUc ) ) ) {
                include_once INCLUDE_DIR . '/app/' . str_replace( 'App' . DIRECTORY_SEPARATOR, '', $classPathUc );
                
                return;
            }
            
        } );
    }
    
}