<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping\Proxy;


use Closure;
use Cycle\ORM\Mapper\Proxy\Hydrator\PropertyMap;
use Cycle\ORM\Reference\ReferenceInterface;
use Cycle\ORM\RelationMap;
use RuntimeException;
use Swift\Orm\Mapping\ClassMetaData;

trait EntityProxyTrait {
    
    public RelationMap $__cycle_orm_rel_map;
    public PropertyMap $__cycle_orm_relation_props;
    public array $__cycle_orm_rel_data = [];
    
    public function __get( string $name ) {
        $relation = $this->__cycle_orm_rel_map->getRelations()[ $name ] ?? null;
        if ( $relation === null ) {
            return method_exists( parent::class, '__get' )
                ? parent::__get( $name )
                : $this->$name;
        }
        
        $value = $this->__cycle_orm_rel_data[ $name ] ?? null;
        if ( $value instanceof ReferenceInterface ) {
            $value       = $relation->collect( $relation->resolve( $value, true ) );
            $this->$name = $value;
            unset( $this->__cycle_orm_rel_data[ $name ] );
            
            return $value;
        }
        
        if ( isset( $this->$name ) ) {
            return $this->$name;
        }
        
        throw new RuntimeException( sprintf( 'Property %s.%s is not initialized.', get_parent_class( static::class ), $name ) );
    }
    
    public function __set( string $name, $value ): void {
        if ( ! array_key_exists( $name, $this->__cycle_orm_rel_map->getRelations() ) ) {
            if ( method_exists( parent::class, '__set' ) ) {
                parent::__set( $name, $value );
            }
            
            return;
        }
        
        if ( $value instanceof ReferenceInterface ) {
            $this->__cycle_orm_rel_data[ $name ] = $value;
            
            return;
        }
        unset( $this->__cycle_orm_rel_data[ $name ] );
        
        $propertyClass = $this->__cycle_orm_relation_props->getPropertyClass( $name );
        if ( $propertyClass === PropertyMap::PUBLIC_CLASS ) {
            $this->$name = $value;
        } else {
            Closure::bind( static function ( object $object, string $property, $value ): void {
                $object->{$property} = $value;
            }, null, $propertyClass )(
                $this, $name, $value
            );
        }
    }
    
    
}