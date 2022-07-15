<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping\Factory\Relations;


use Swift\Orm\Mapping\ClassMetaData;
use Swift\Orm\Mapping\Definition\Entity;
use Swift\Orm\Mapping\Definition\Field;
use Swift\Orm\Mapping\Definition\Index;
use Swift\Orm\Mapping\Definition\IndexType;
use Swift\Orm\Mapping\Definition\Relation\EntitiesConnection;
use Swift\Orm\Mapping\Definition\Relation\EntityRelationType;
use Swift\Orm\Mapping\Definition\Relation\RelationInterface;
use Swift\Orm\Mapping\RegistryInterface;
use Swift\Orm\Types\FieldTypes;
use Swift\Orm\Types\Integer;

class ManyToManyRelationMetaDataFactory extends AbstractRelationMetaDataFactory {
    
    /**
     * @inheritDoc
     */
    public function supports( \ReflectionProperty $property, ClassMetaData $classMetadata, RegistryInterface $registry ): bool {
        $attribute = $this->attributeReader->getRelationAttribute( $property );
        
        if ( $attribute?->getRelationType() !== EntityRelationType::MANY_TO_MANY ) {
            return false;
        }
        
        // Only support if it has not been created before
        return !$registry->hasEntitiesConnection(
            [
                $classMetadata->getEntity()->getClassName(),
                $registry->getClassMetaData( $attribute->getTargetEntity() )->getEntity()->getClassName(),
            ]
        );
    }
    
    public function createRelationMetaData( \ReflectionProperty $property, ClassMetaData $classMetaData, RegistryInterface $registry ): void {
        $relation = $this->createRelation( $property, $classMetaData, $registry );
        
        if (!$relation) {
            throw new \RuntimeException( 'Could not create relation metadata' );
        }
        
        $targetEntityClassMetaData = $registry->getClassMetaData( $relation->getTargetEntity() );
    
        $currentEntityFieldName = $this->entityMappingNamingStrategy->getEntitiesConnectionFieldName( $classMetaData->getEntity(), $relation->getCurrentEntityField() );
        $targetEntityFieldName = $this->entityMappingNamingStrategy->getEntitiesConnectionFieldName( $targetEntityClassMetaData?->getEntity(), $relation->getTargetEntityField() );
        
        $joiningRelation = $this->createJoiningRelation( $targetEntityClassMetaData, $relation->getTargetEntityField(), $classMetaData->getEntity()->getClassName(), $registry );
    
        $fields = [
            new Field(
                'id',
                'id',
                new \Swift\Orm\Attributes\Field( name: 'id', primary: true, length: 11 ),
                new Integer(),
                null,
                [],
                null,
                IndexType::PRIMARY,
            ),
            new Field(
                $currentEntityFieldName,
                $currentEntityFieldName,
                new \Swift\Orm\Attributes\Field( name: $currentEntityFieldName, type: FieldTypes::INT, length: 11, index: true ),
                new Integer(),
                null,
                [],
                null,
                IndexType::INDEX,
            ),
            new Field(
                $targetEntityFieldName,
                $targetEntityFieldName,
                new \Swift\Orm\Attributes\Field( name: $targetEntityFieldName, type: FieldTypes::INT, length: 11, index: true ),
                new Integer(),
                null,
                [],
                null,
                IndexType::INDEX,
            ),
        ];
    
        $relationEntity = new Entity(
            'fictional',
            $this->entityMappingNamingStrategy->getEntitiesConnectionEntityName(
                [ $classMetaData->getEntity(), $targetEntityClassMetaData?->getEntity() ],
                [ $relation->getCurrentEntityField(), $relation->getTargetEntityField() ]
            ),
            $this->configuration->get( 'connection.prefix', 'database' ),
            null,
            $fields[ 0 ],
            $fields,
            [],
            [],
        );
    
        $relationEntity->addIndex(
            new Index(
                $this->entityMappingNamingStrategy->getIndexName( $relationEntity, [ $fields[ 0 ] ], IndexType::PRIMARY ),
                IndexType::PRIMARY,
                [ $fields[ 0 ] ],
            ),
        );
        $relationEntity->addIndex(
            new Index(
                $this->entityMappingNamingStrategy->getIndexName( $relationEntity, [ $fields[ 1 ] ], IndexType::INDEX ),
                IndexType::INDEX,
                [ $fields[ 1 ] ],
            ),
        );
        $relationEntity->addIndex(
            new Index(
                $this->entityMappingNamingStrategy->getIndexName( $relationEntity, [ $fields[ 2 ] ], IndexType::INDEX ),
                IndexType::INDEX,
                [ $fields[ 2 ] ],
            ),
        );
    
        $relation->setEntity( $relationEntity );
        $joiningRelation?->setEntity( $relationEntity );
        
        
        
        $connection = new EntitiesConnection( $relation->getEntityRelationType() );
        $connection->addEntity( $relation->getCurrentEntityField(), $classMetaData, $relation );
        $connection->addEntity( $relation->getTargetEntityField(), $targetEntityClassMetaData, $joiningRelation );
        $connection->setConnectorEntity( $relationEntity );
        $connection->setLeadingEntity( $relationEntity );
    
        $classMetaData->getEntity()->addConnection( $connection );
        $targetEntityClassMetaData->getEntity()->addConnection( $connection );
        
        $registry->setEntitiesConnection( $connection );
    }
    
    protected function createJoiningRelation( ClassMetadata $classMetaData, string $fieldName, string $targetEntity, \Swift\Orm\Mapping\RegistryInterface $registry ): ?RelationInterface {
        foreach ( $classMetaData->getReflectionClass()->getProperties() as $property ) {
            $relationAttribute = $this->attributeReader->getRelationAttribute( $property );
            
            if (!$relationAttribute) {
                continue;
            }
            
            if (($relationAttribute->getTargetEntity() !== $targetEntity) || ($classMetaData->getEntity()->getPrimaryKey()->getDatabaseName() !== $fieldName)) {
                continue;
            }
            
            return $this->createRelation( $property, $classMetaData, $registry );
        }
        
        return null;
    }
    
    
}