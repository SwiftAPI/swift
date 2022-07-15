<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Type;


class TypeFactory {
    
    public static DateTimeType $dateTime;
    public static UuidType $uuid;
    public static JsonType $json;
    
    public static function dateTime(): DateTimeType {
        if ( ! isset( self::$dateTime ) ) {
            self::$dateTime = new DateTimeType();
        }
        
        return self::$dateTime;
    }
    
    public static function uuid(): UuidType {
        if ( ! isset( self::$uuid ) ) {
            self::$uuid = new UuidType();
        }
        
        return self::$uuid;
    }
    
    public static function json(): JsonType {
        if ( ! isset( self::$json ) ) {
            self::$json = new JsonType();
        }
        
        return self::$json;
    }
    
}