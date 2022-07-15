<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping\Driver;

use ReflectionProperty;
use Swift\Code\ReflectionFactory;
use Swift\Dbal\Exceptions\InvalidConfigurationException;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Orm\Attributes\Embeddable;
use Swift\Orm\Attributes\Index;
use Swift\Orm\Attributes\Relation\Embedded;
use Swift\Orm\Attributes\Relation\ManyToMany;
use Swift\Orm\Attributes\Relation\HasOne;
use Swift\Orm\Attributes\Relation\HasMany;
use Swift\Orm\Attributes\Relation\BelongsTo;
use Swift\Orm\Attributes\Relation\RefersTo;
use Swift\Orm\Attributes\Relation\RelationFieldInterface;
use Swift\Orm\Attributes\Entity;
use Swift\Orm\Attributes\Relation\RelationInterface;


/**
 * Class AttributeReader
 * @package Swift\Orm\Mapping\Driver
 */
#[Autowire]
class AttributeReader {
    
    /**
     * AttributeReader constructor.
     */
    public function __construct(
        private readonly ReflectionFactory $reflectionFactory,
    ) {
    }
    
    public function getEntityAttribute( string $className ): Entity {
        $reflection        = $this->reflectionFactory->getReflectionClass( $className );
        $dbEntityAttribute = $this->reflectionFactory->getAttributeReader()->getClassAnnotation( $reflection, Entity::class );
        
        if ( ! $dbEntityAttribute ) {
            throw new InvalidConfigurationException( sprintf( 'Entity %s missing Entity attribute, this is an invalid use case. Please add %s attribute to class', $className, Entity::class ) );
        }
        
        return $dbEntityAttribute;
    }
    
    /**
     * @param string $className
     *
     * @return Index[]
     */
    public function getIndexAttributes( string $className ): array {
        $reflection = $this->reflectionFactory->getReflectionClass( $className );
        $attributes = $this->reflectionFactory->getAttributeReader()->getClassAnnotation( $reflection, Index::class );
        
        if (! $attributes ) {
            return [];
        }
        
        return is_array( $attributes ) ? $attributes : [ $attributes ];
    }
    
    /**
     * @param \ReflectionProperty $property
     *
     * @return \Swift\Orm\Attributes\Field|null
     */
    public function getFieldAttribute( ReflectionProperty $property ): ?\Swift\Orm\Attributes\Field {
        $attributes = $property->getAttributes( \Swift\Orm\Attributes\Field::class );
        
        if ( empty( $attributes ) ) {
            return null;
        }
        
        return $attributes[ 0 ]?->newInstance();
    }
    
    public function getRelationAttribute( ReflectionProperty $property ): RelationFieldInterface|RelationInterface|null {
        $attributes = [
            ...$property->getAttributes( RefersTo::class ),
            ...$property->getAttributes( BelongsTo::class ),
            ...$property->getAttributes( HasOne::class ),
            ...$property->getAttributes( HasMany::class ),
            ...$property->getAttributes( ManyToMany::class ),
            ...$property->getAttributes( Embedded::class ),
        ];
        
        if ( empty( $attributes ) ) {
            return null;
        }
        
        return $attributes[ 0 ]?->newInstance();
    }

    public function getEmbeddableAttribute( string $className ): Embeddable {
        $reflection        = $this->reflectionFactory->getReflectionClass( $className );
        $dbEntityAttribute = $this->reflectionFactory->getAttributeReader()->getClassAnnotation( $reflection, Embeddable::class );
        
        if ( ! $dbEntityAttribute ) {
            throw new InvalidConfigurationException( sprintf( 'Entity %s missing Entity attribute, this is an invalid use case. Please add %s attribute to class', $className, Embeddable::class ) );
        }
        
        return $dbEntityAttribute;
    }
    
}