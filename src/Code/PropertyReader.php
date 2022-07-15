<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Code;

use Swift\Code\Exception\InaccessiblePropertyException;
use Swift\DependencyInjection\Attributes\Autowire;

#[Autowire]
class PropertyReader {
    
    /**
     * Read property values from given class
     *
     * @param object $classItem
     * @param string $propertyName
     *
     * @return mixed
     */
    public function getPropertyValue( object $classItem, string $propertyName ): mixed {
        $closure = \Closure::bind( static function ( object $classItem, string $propertyName ): mixed {
            return $classItem->{$propertyName} ?? throw new InaccessiblePropertyException( 'Could not access property' );
        }, null, $classItem);
        
        return $closure( $classItem, $propertyName );
    }
    
    /**
     * Write property values from given class
     *
     * @param object $classItem
     * @param string $propertyName
     * @param mixed  $propertyValue
     *
     * @return object
     */
    public function setPropertyValue( object $classItem, string $propertyName, mixed $propertyValue ): object {
        $closure = \Closure::bind( static function ( object &$classItem, string $propertyName, mixed $propertyValue ): object {
            $classItem->{$propertyName} = $propertyValue;
            
            return $classItem;
        }, null, $classItem);
        
        return $closure( $classItem, $propertyName, $propertyValue );
    }
    
    public function unsetProperty( object $classItem, string $propertyName ): object {
        $closure = \Closure::bind( static function ( object &$classItem, string $propertyName ): object {
            unset( $classItem->{$propertyName} );
            
            return $classItem;
        }, null, $classItem);
        
        return $closure( $classItem, $propertyName );
    }
    
}