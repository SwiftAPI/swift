<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping;


use JetBrains\PhpStorm\ArrayShape;
use Swift\Code\Reflection\DummyReflectionProperty;
use Swift\Orm\Attributes\Relation\RelationFieldInterface;
use Swift\Orm\Exceptions\EntityNotRegisteredException;
use Swift\Orm\Mapping\Definition\Relation\EntityRelationType;
use Swift\Orm\Mapping\Driver\AttributeReader;

class Utils {
    
    public static function getRelations( \ReflectionProperty $declaringProperty, RegistryInterface $registry, AttributeReader $attributeReader ): array {
        $declaringAttribute = $attributeReader->getRelationAttribute( $declaringProperty );
        $owningSide         = self::determineOwningSide( $declaringProperty, $registry, $attributeReader );
        $passiveSide        = self::determinePassiveSide( $declaringProperty, $registry, $attributeReader );
        
        if ( ! $owningSide || ! $passiveSide ) {
            throw new EntityNotRegisteredException( sprintf( 'Could not locate the given entities for the %s relation in %s on %s', $declaringAttribute->getRelationType()->name, $declaringProperty->getDeclaringClass()->getName(), $declaringProperty->getName() ) );
        }
        
        $declaringIsOwner = $declaringProperty->getDeclaringClass()->getName() === $owningSide->getEntity()->getClassName();
        
        [ $ownerProperty, $ownerAttribute ] = $declaringIsOwner ? [ $declaringProperty, $declaringAttribute ] : self::findOppositeRelationAttributeAndProperty( $declaringAttribute, $registry, $attributeReader, $passiveSide->getEntity()->getClassName() );
        [ $passiveProperty, $passiveAttribute ] = $declaringIsOwner ? self::findOppositeRelationAttributeAndProperty( $declaringAttribute, $registry, $attributeReader, $owningSide->getEntity()->getClassName() ) : [ $declaringProperty, $declaringAttribute ];
        
        return [
            'owning'  => [
                'classMetaData' => $owningSide,
                'attribute'     => $ownerAttribute,
                'property'      => $ownerProperty,
                'shouldRender'  => (bool) $ownerProperty,
            ],
            'passive' => [
                'classMetaData' => $passiveSide,
                'attribute'     => $passiveAttribute,
                'property'      => $passiveProperty,
                'shouldRender'  => (bool) $passiveProperty,
            ],
        ];
    }
    
    public static function determineOwningSide( \ReflectionProperty $property, RegistryInterface $registry, AttributeReader $attributeReader ): ?ClassMetaData {
        return self::determineOwningAndPassiveRelationSides( $property, $registry, $attributeReader )[ 'owner' ];
    }
    
    #[ArrayShape( [ 'owner' => "null|\Swift\Orm\Mapping\ClassMetaData", 'passive' => "null|\Swift\Orm\Mapping\ClassMetaData" ] )]
    public static function determineOwningAndPassiveRelationSides( \ReflectionProperty $property, RegistryInterface $registry, AttributeReader $attributeReader ): array {
        $attribute = $attributeReader->getRelationAttribute( $property );
        
        $owner   = ( $attribute->getRelationType() === EntityRelationType::HAS_ONE ) || ( $attribute->getRelationType() === EntityRelationType::BELONGS_TO ) ?
            $registry->getClassMetaData( $property->getDeclaringClass()->getName() ) : $registry->getClassMetaData( $attribute->getTargetEntity() );
        $passive = ( $attribute->getRelationType() !== EntityRelationType::HAS_ONE ) && ( $attribute->getRelationType() !== EntityRelationType::BELONGS_TO ) ?
            $registry->getClassMetaData( $property->getDeclaringClass()->getName() ) : $registry->getClassMetaData( $attribute->getTargetEntity() );
        
        return [
            'owner'   => $owner,
            'passive' => $passive,
        ];
    }
    
    public static function determinePassiveSide( \ReflectionProperty $property, RegistryInterface $registry, AttributeReader $attributeReader ): ?ClassMetaData {
        return self::determineOwningAndPassiveRelationSides( $property, $registry, $attributeReader )[ 'passive' ];
    }
    
    public static function findOppositeRelationAttributeAndProperty( RelationFieldInterface $relationAttribute, RegistryInterface $registry, AttributeReader $attributeReader, string $matchAgainst ): ?array {
        $target = $registry->getClassMetaData( $relationAttribute->getTargetEntity() );
        
        foreach ( $target->getReflectionClass()->getProperties() as $property ) {
            $attribute = $attributeReader->getRelationAttribute( $property );
            if ( $attribute?->getTargetEntity() === $matchAgainst ) {
                return [ $property, $attribute ];
            }
        }
    
        if ($relationAttribute->getInverse()) {
            return [
                new DummyReflectionProperty( $relationAttribute->getInverse()->getAs(), $relationAttribute->getTargetEntity() ),
                $relationAttribute->getInverse()->getRelationType()->toRelationAttribute( $matchAgainst ),
            ];
        }
        
        return [
            null,
            null
        ];
    }
    
}