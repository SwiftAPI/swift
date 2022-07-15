<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Utilities;

use Swift\Code\Exception\InaccessiblePropertyException;
use Swift\Code\ReflectionFactory;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Orm\Entity\EntityInterface;
use Swift\Orm\Mapping\ClassMetaDataFactory;

/**
 * Property reader with ability to read private properties from given object
 */
#[Autowire]
class EntityPropertyReader {
    
    public function __construct(
        protected ReflectionFactory $reflectionFactory,
        protected ClassMetaDataFactory $classMetaDataFactory,
    ) {
    }
    
    public function getEntityFields( EntityInterface $entity ): array {
        $entityDefinition = $this->classMetaDataFactory->getClassMetaData( $entity::class )?->getEntity();
        $values = [];
        
        foreach( $entityDefinition->getFields() as $field ) {
            try {
                $value = $this->reflectionFactory->getPropertyReader()->getPropertyValue( $entity, $field->getPropertyName() );
                $values[ $field->getPropertyName() ] = $value;
            } catch (InaccessiblePropertyException) {}
        }
    
        return $values;
    }
    
    public function getEntityRelationValues( EntityInterface $entity ): array {
        $entityDefinition = $this->classMetaDataFactory->getClassMetaData( $entity::class )?->getEntity();
        $values = [];
    
        foreach( $entityDefinition->getConnections() as $connection ) {
            $relation = $connection->getRelation( $entity::class );
            try {
                $value = $this->reflectionFactory->getPropertyReader()->getPropertyValue( $entity, $relation->getName() );
                $values[ $relation->getName() ] = $value;
                //$values[ $relation->getName() ] = is_array( $value ) ? array_map( static fn($item): array => $this->getEntityFields($item), $value ) : $this->getEntityFields( $value );
            } catch (InaccessiblePropertyException) {}
        }
        
        return $values;
    }
    
}