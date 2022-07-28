<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\DependencyInjection\Helper;


use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\DependencyInjection\Attributes\DI;

class AttributeHelper {
    
    /**
     * @param \ReflectionClass $reflection
     *
     * @return DI[]|\Swift\Kernel\Attributes\DI[]|null
     */
    public static function getDiAttributes( ReflectionClass $reflection ): ?array {
        $attributes = $reflection->getAttributes( DI::class );
        
        if ( ! empty( $attributes ) ) {
            return $attributes;
        }
        
        $attributesDeprecated = $reflection->getAttributes( \Swift\Kernel\Attributes\DI::class );
        
        if ( ! empty( $attributesDeprecated ) ) {
            return $attributesDeprecated;
        }
        
        return null;
    }
    
    /**
     * @param \ReflectionClass|\ReflectionMethod|\ReflectionParameter|\ReflectionProperty $reflection
     *
     * @return  \Swift\DependencyInjection\Attributes\Autowire[]|\Swift\Kernel\Attributes\Autowire[]|null
     */
    public static function getAutowireAttributes( ReflectionClass|ReflectionMethod|ReflectionParameter|\ReflectionProperty $reflection ): ?array {
        $attributes = $reflection->getAttributes( Autowire::class );
        
        if ( ! empty( $attributes ) ) {
            return $attributes;
        }
        
        $attributesDeprecated = $reflection->getAttributes( \Swift\Kernel\Attributes\Autowire::class );
        
        if ( ! empty( $attributesDeprecated ) ) {
            return $attributesDeprecated;
        }
        
        return null;
    }
    
}