<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping\Factory\Relations;


use Swift\Code\Reflection\DummyReflectionProperty;
use Swift\Orm\Exceptions\EntityNotRegisteredException;
use Swift\Orm\Mapping\ClassMetaData;
use Swift\Orm\Mapping\Definition\Field;
use Swift\Orm\Mapping\Definition\Index;
use Swift\Orm\Mapping\Definition\IndexType;
use Swift\Orm\Mapping\Definition\Relation\EntitiesConnection;
use Swift\Orm\Mapping\Definition\Relation\EntityRelationType;
use Swift\Orm\Mapping\Definition\Relation\Relation;
use Swift\Orm\Mapping\RegistryInterface;
use Swift\Orm\Mapping\Utils;
use Swift\Orm\Types\FieldTypes;
use Swift\Orm\Types\Integer;

class HasOrBelongsToManyRelationMetaDataFactory extends AbstractRelationMetaDataFactory {
    
    /**
     * @inheritDoc
     */
    public function supports( \ReflectionProperty $property, ClassMetaData $classMetadata, RegistryInterface $registry ): bool {
        $attribute = $this->attributeReader->getRelationAttribute( $property );
        
        if ( ! in_array(
            $attribute?->getRelationType()->name,
            [ EntityRelationType::HAS_ONE->name, EntityRelationType::HAS_MANY->name, EntityRelationType::BELONGS_TO->name ],
            true,
        ) ) {
            return false;
        }
        
        // Only support if it has not been created before
        return ! $registry->hasEntitiesConnection(
            [
                $classMetadata->getEntity()->getClassName(),
                $registry->getClassMetaData( $attribute->getTargetEntity() )->getEntity()->getClassName(),
            ]
        );
    }
    
    public function createRelationMetaData( \ReflectionProperty $property, ClassMetaData $classMetaData, RegistryInterface $registry ): void {
        
        // Flow one-one/many relations
        // 1. Determine which entity needs to have the relational field in the database
        // 2. Determine whether relation should inverse
        // 3. Add fields to entities
        // 4. Create relations
        
        // HasOne -> is the direct owner
        // BelongsTo -> is the direct owner, is similar to HasOne, but will always inverse
        // HasMany -> many side is the direct owner
        
        // 1. Determine which entity needs to have the relational field in the database
        $relationData        = Utils::getRelations( $property, $registry, $this->attributeReader );
        $owningSide          = $relationData[ 'owning' ][ 'classMetaData' ];
        $owningAttribute     = $relationData[ 'owning' ][ 'attribute' ];
        $owningProperty      = $relationData[ 'owning' ][ 'property' ];
        $owningShouldRender  = $relationData[ 'owning' ][ 'shouldRender' ];
        $passiveSide         = $relationData[ 'passive' ][ 'classMetaData' ];
        $passiveAttribute    = $relationData[ 'passive' ][ 'attribute' ];
        $passiveProperty     = $relationData[ 'passive' ][ 'property' ];
        $passiveShouldRender = $relationData[ 'passive' ][ 'shouldRender' ];
        
        if ( ! $owningSide || ! $passiveSide ) {
            throw new EntityNotRegisteredException( 'Could not build relation' );
        }
        
        // 2. Determine whether relation should inverse
        // TODO: Check and perform inversification
        
        // 3. Add fields to entities
        $connectionField = $this->addFieldToOwner( $owningSide, $passiveSide, $passiveProperty );
        
        // 4. Create relations
        $owningRelation = Relation::createRelation(
            $owningProperty->getName(),
            $owningAttribute->getRelationType(),
            $owningAttribute->getTargetEntity(),
            $passiveSide->getEntity()->getPrimaryKey()->getPropertyName(),
            $connectionField->getPropertyName(),
            true,
        );
        if ( ! $owningRelation ) {
            throw new \RuntimeException( 'Could not create relation metadata' );
        }
        $owningRelation->setEntity( $owningSide->getEntity() );
        
        $passiveEntityRelation = $passiveShouldRender ? Relation::createRelation(
            $passiveProperty->getName(),
            $passiveAttribute->getRelationType(),
            $passiveAttribute->getTargetEntity(),
            $connectionField->getPropertyName(),
            $passiveSide->getEntity()->getPrimaryKey()->getPropertyName(),
            false,
        ) : null;
        $passiveEntityRelation?->setEntity( $passiveSide->getEntity() );
        
        
        // Build connection
        $connection = new EntitiesConnection( $owningRelation->getEntityRelationType() );
        $connection->addEntity( $owningRelation->getCurrentEntityField(), $owningSide, $owningRelation );
        $connection->addEntity( $owningRelation->getTargetEntityField(), $passiveSide, $passiveEntityRelation );
        
        if ( $owningShouldRender ) {
            $owningSide->getEntity()->addConnection( $connection );
        }
        if ( $passiveShouldRender ) {
            $passiveSide->getEntity()->addConnection( $connection );
        }
        
        
        // Add connection to registry
        $registry->setEntitiesConnection( $connection );
    }
    
    /**
     * @param \Swift\Orm\Mapping\ClassMetaData                                        $owner          Owning side of the relation; the entity that will store the relation data
     * @param \Swift\Orm\Mapping\ClassMetaData                                        $referringClass Class referring the id
     * @param \ReflectionProperty|\Swift\Code\Reflection\DummyReflectionProperty|null $property
     *
     * @return Field
     */
    private function addFieldToOwner( ClassMetaData $owner, ClassMetaData $referringClass, \ReflectionProperty|DummyReflectionProperty|null $property ): Field {
        $relationFieldName = $this->entityMappingNamingStrategy->getEntitiesConnectionFieldName( $referringClass->getEntity(), $referringClass->getEntity()->getPrimaryKey()->getDatabaseName() );
        
        $connectionField = new Field(
            $property?->getName() ?? $relationFieldName,
            $relationFieldName,
            new \Swift\Orm\Attributes\Field( name: $relationFieldName, type: FieldTypes::INT, length: 11, index: true, comment: sprintf( 'Relation with %s', str_replace( '\\', '/', $referringClass->getEntity()->getClassName() ) ) ),
            new Integer(),
            null,
            [],
            null,
            IndexType::INDEX,
            true,
        );
        $owner->getEntity()->addField( $connectionField );
        $owner->getEntity()->addIndex(
            new Index(
                $this->entityMappingNamingStrategy->getIndexName( $referringClass->getEntity(), [ $connectionField ], IndexType::INDEX ),
                IndexType::INDEX,
                [ $connectionField ]
            ),
        );
        
        return $connectionField;
    }
    
}