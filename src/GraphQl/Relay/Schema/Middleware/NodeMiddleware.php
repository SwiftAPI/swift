<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Relay\Schema\Middleware;


use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Swift\GraphQl\Relay\Relay;
use Swift\GraphQl\Schema\Registry;

class NodeMiddleware implements \Swift\GraphQl\Schema\Middleware\SchemaMiddlewareInterface {
    
    /**
     * @inheritDoc
     */
    public function process( mixed $builder, Registry $registry, callable $next ): mixed {
        if ( ! $builder instanceof \Swift\GraphQl\Schema\Builder\ObjectBuilder ) {
            return $next( $builder, $registry );
        }
        
        $interfaces = $builder->getInterfaces();
        
        if ( ! isset( $interfaces[ Relay::NODE ] ) ) {
            return $next( $builder, $registry );
        }
        
        $fields = $builder->getFields();
        
        if ( is_callable( $fields ) ) {
            throw new \InvalidArgumentException( 'Fields must be an array for usage with Relay' );
        }
        
        $fields[ 'id' ][ 'resolve' ] = static function ( $object, array $args, $context, ResolveInfo $info ) use ( $builder, $fields ) {
            $val = is_callable( $fields[ 'id' ][ 'resolve' ] ) ? $fields[ 'id' ][ 'resolve' ]( $object, $args, $context, $info ) : $object->id;
            
            $val ??= $object->id;
            
            if ( ! $val ) {
                return $val;
            }
            
            return Relay::encodeId( $builder->getName(), $val );
        };
        $fields[ 'incrementId' ]     = [
            'type'        => Type::nonNull( Type::int() ),
            'description' => 'The incremental id of the node',
            'resolve'     => static function ( $object, array $args, $context, ResolveInfo $info ) use ( $fields ) {
                return ! empty( $fields[ 'incrementId' ][ 'resolve' ] ) && is_callable( $fields[ 'incrementId' ][ 'resolve' ] ) ?
                    $fields[ 'incrementId' ][ 'resolve' ]( $object, $args, $context, $info ) : $object->id;
            },
        ];
        
        $builder->setFields( $fields );
        
        return $next( $builder, $registry );
    }
    
}