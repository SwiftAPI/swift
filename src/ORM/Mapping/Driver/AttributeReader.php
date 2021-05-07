<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\ORM\Mapping\Driver;

use Attribute;
use Doctrine\ORM\Mapping\Annotation;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

use function count;
use function is_subclass_of;

/**
 * Class AttributeReader
 * @package Swift\ORM\Mapping\Driver
 */
class AttributeReader {

    /** @var array<string,bool> */
    private array $isRepeatableAttribute = [];

    /** @return array<object>|object|null */
    public function getClassAnnotation( ReflectionClass $class, $annotationName ) {
        return $this->getClassAnnotations( $class )[ $annotationName ] ?? ( $this->isRepeatable( $annotationName ) ? [] : null );
    }

    private function isRepeatable( string $attributeClassName ): bool {
        if ( isset( $this->isRepeatableAttribute[ $attributeClassName ] ) ) {
            return $this->isRepeatableAttribute[ $attributeClassName ];
        }

        $reflectionClass = new ReflectionClass( $attributeClassName );

        if (empty($reflectionClass->getAttributes())) {
            return false;
        }

        $attribute       = $reflectionClass->getAttributes()[0]->newInstance();

        return $this->isRepeatableAttribute[ $attributeClassName ] = ( $attribute->flags & Attribute::IS_REPEATABLE ) > 0;
    }

    /** @return array<object> */
    public function getClassAnnotations( ReflectionClass $class ): array {
        return $this->convertToAttributeInstances( $class->getAttributes() );
    }

    /**
     * @param array<object> $attributes
     *
     * @return array<Annotation>
     */
    private function convertToAttributeInstances( array $attributes ): array {
        $instances = [];

        foreach ( $attributes as $attribute ) {
            // Make sure we only get Doctrine Annotations
            if ( ! is_subclass_of( $attribute->getName(), Annotation::class ) ) {
                continue;
            }

            $instance = $attribute->newInstance();

            if ( $this->isRepeatable( $attribute->getName() ) ) {
                $instances[ $attribute->getName() ][] = $instance;
            } else {
                $instances[ $attribute->getName() ] = $instance;
            }
        }

        return $instances;
    }

    /** @return array<object>|object|null */
    public function getMethodAnnotation( ReflectionMethod $method, $annotationName ) {
        return $this->getMethodAnnotations( $method )[ $annotationName ] ?? ( $this->isRepeatable( $annotationName ) ? [] : null );
    }

    /** @return array<object> */
    public function getMethodAnnotations( ReflectionMethod $method ): array {
        return $this->convertToAttributeInstances( $method->getAttributes() );
    }

    /** @return array<object>|object|null */
    public function getPropertyAnnotation( ReflectionProperty $property, $annotationName ) {
        return $this->getPropertyAnnotations( $property )[ $annotationName ] ?? ( $this->isRepeatable( $annotationName ) ? [] : null );
    }

    /** @return array<object> */
    public function getPropertyAnnotations( ReflectionProperty $property ): array {
        return $this->convertToAttributeInstances( $property->getAttributes() );
    }

}