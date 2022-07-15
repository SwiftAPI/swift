<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Deprecations;


final class Deprecation {
    
    private static DeprecationLevel $deprecationLevel = DeprecationLevel::TRIGGER_ERROR;
    
    public static function getDeprecationLevel(): DeprecationLevel {
        return self::$deprecationLevel;
    }
    
    public static function setDeprecationLevel( DeprecationLevel $deprecationLevel ): void {
        self::$deprecationLevel = $deprecationLevel;
    }
    
    public static function trigger( string $package, string $link, string $message, ...$args ): void {
        if ( self::$deprecationLevel === DeprecationLevel::NONE ) {
            return;
        }
        
        $message = sprintf( $message, ...$args );
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        
        self::delegateTriggerToBackend(
            $message,
            $backtrace,
            $link,
            $package,
        );
    }
    
    /**
     * @param array<mixed> $backtrace
     */
    private static function delegateTriggerToBackend( string $message, array $backtrace, string $link, string $package ): void {
        $message .= sprintf(
            ' (%s:%d called by %s:%d, %s, package %s)',
            self::basename( $backtrace[ 0 ][ 'file' ] ),
            $backtrace[ 0 ][ 'line' ],
            self::basename( $backtrace[ 1 ][ 'file' ] ),
            $backtrace[ 1 ][ 'line' ],
            $link,
            $package
        );
        
        @trigger_error( $message, E_USER_DEPRECATED );
    }
    
    /**
     * A non-local-aware version of PHPs basename function.
     */
    private static function basename( string $filename ): string {
        $pos = strrpos( $filename, DIRECTORY_SEPARATOR );
        
        if ( $pos === false ) {
            return $filename;
        }
        
        return substr( $filename, $pos + 1 );
    }
    
}