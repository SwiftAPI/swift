<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Relay;


use GraphQL\Type\Definition\ResolveInfo;
use JetBrains\PhpStorm\ArrayShape;
use Swift\GraphQl\Schema\Registry;

class Relay {
    
    public static array $resolverAliases = [];
    
    public const CONNECTION = 'Connection';
    public const EDGE       = 'Edge';
    public const NODE       = 'Node';
    public const PAGE_INFO  = 'PageInfo';
    
    public const ID_SEPARATOR = ':';
    
    public static function encodeId( string $name, int|string $id ): string {
        return base64_encode( $name . self::ID_SEPARATOR . $id );
    }
    
    #[ArrayShape( [ 'name' => "string", 'id' => "string" ] )]
    public static function decodeId( string $id ): array {
        [ $name, $id ] = explode( self::ID_SEPARATOR, base64_decode( $id ) );
        
        return [
            'name' => $name,
            'id'   => $id,
        ];
    }
    
    public static function resolveNode( $value, $args, $context, ResolveInfo $info ): mixed {
        [ 'name' => $name, 'id' => $id ] = self::decodeId( $args[ 'id' ] );
        
        if ( ! isset( Registry::$typeMap[ $name ] ) ) {
            throw new \Exception( "No schema registered for '$name'" );
        }
        
        $info->fieldDefinition->name = $name;
        $args['id'] = $id;
    
        return Registry::$typeMap[ $name ]->config['resolveField']( $value, $args, $context, $info );
    }
    
}