<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping\Definition\Relation;

use Swift\Orm\Cache\EntityMappingCache;
use Swift\Orm\Mapping\ClassMetaData;

/**
 * Represents a connection between entities
 */
class EntitiesConnection {
    
    private array $mapping = [];
    /** @var \Swift\Orm\Mapping\ClassMetaData[] $entities Participating entities in connection (usually these are entities with relation attributes) */
    private array $entities = [];
    /** @var \Swift\Orm\Mapping\Definition\Relation\Relation[] $relations Basic representation of entity relation config */
    private array $relations = [];
    /** @var \Swift\Orm\Mapping\Definition\Entity|null $leadingEntity Leading entity on which to join (either the connector entity or one of participating entities) */
    private ?\Swift\Orm\Mapping\Definition\Entity $leadingEntity = null;
    /** @var \Swift\Orm\Mapping\Definition\Entity|null $connectorEntity Fiction entity generated to connect two entities together */
    private ?\Swift\Orm\Mapping\Definition\Entity $connectorEntity = null;
    
    /**
     * @param \Swift\Orm\Mapping\Definition\Relation\EntityRelationType $entityRelationType
     */
    public function __construct(
        private readonly EntityRelationType $entityRelationType,
    ) {
    }
    
    /**
     * @param string                                                                                                        $entityFieldName
     * @param \Swift\Orm\Mapping\ClassMetaData                                                                              $entity
     * @param \Swift\Orm\Mapping\Definition\Relation\RelationInterface|\Swift\Orm\Mapping\Definition\Relation\Relation|null $relation
     */
    public function addEntity( string $entityFieldName, \Swift\Orm\Mapping\ClassMetaData $entity, RelationInterface|Relation|null $relation ): void {
        $this->entities[ $entity->getEntity()->getClassName() ] = $entity;
        
        if ( ! isset( $this->mapping[ $entity->getEntity()->getClassName() ] ) ) {
            $this->mapping[ $entity->getEntity()->getClassName() ] = [];
        }
        
        $this->mapping[ $entity->getEntity()->getClassName() ] = $entityFieldName;
        
        $this->relations[ $entity->getEntity()->getClassName() ] = $relation;
    }
    
    /**
     * @return \Swift\Orm\Mapping\ClassMetaData[]
     */
    public function getEntities(): array {
        return $this->entities;
    }
    
    public function getEntity( string $name ): ?ClassMetaData {
        return $this->entities[ $name ] ?? null;
    }
    
    /**
     * Get field (property) name in given entity
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getName( string $name ): ?string {
        return $this->getRelation( $name )?->getName();
    }
    
    public function getRelation( string $name ): ?Relation {
        return $this->relations[ $name ] ?? null;
    }
    
    public function getTargetEntityField( string $name ): ?string {
        return $this->relations[$name]?->getTargetEntityField();
    }
    
    public function getTargetEntity( string $name ): ?string {
        if ( count( $this->entities ) !== 2 ) {
            throw new \InvalidArgumentException( 'Connections only support relations between two objects' );
        }
        
        foreach ( $this->entities as $entity ) {
            if ( $entity->getEntity()->getClassName() !== $name ) {
                return $entity->getEntity()->getClassName();
            }
        }
        
        return null;
    }
    
    public function getEntityField( string $name ): ?string {
        return $this->relations[$name]?->getCurrentEntityField();
    }
    
    /**
     * @return \Swift\Orm\Mapping\Definition\Entity|null
     */
    public function getLeadingEntity(): ?\Swift\Orm\Mapping\Definition\Entity {
        return $this->leadingEntity;
    }
    
    /**
     * @param \Swift\Orm\Mapping\Definition\Entity $leadingEntity
     */
    public function setLeadingEntity( \Swift\Orm\Mapping\Definition\Entity $leadingEntity ): void {
        if ( ! in_array( $leadingEntity->getClassName(), $this->entities, true ) && ( $leadingEntity->getDatabaseName() !== $this->connectorEntity?->getDatabaseName() ) ) {
            throw new \InvalidArgumentException( 'Setting a leading entity which is not represented as participating or connector entity is not possible' );
        }
        
        $this->leadingEntity = $leadingEntity;
    }
    
    /**
     * @return \Swift\Orm\Mapping\Definition\Relation\EntityRelationType
     */
    public function getEntityRelationType(): EntityRelationType {
        return $this->entityRelationType;
    }
    
    /**
     * @return \Swift\Orm\Mapping\Definition\Entity|null
     */
    public function getConnectorEntity(): ?\Swift\Orm\Mapping\Definition\Entity {
        return $this->connectorEntity;
    }
    
    /**
     * @param \Swift\Orm\Mapping\Definition\Entity|null $connectorEntity
     */
    public function setConnectorEntity( ?\Swift\Orm\Mapping\Definition\Entity $connectorEntity ): void {
        $this->connectorEntity = $connectorEntity;
    }
    
    public function hasConnectorEntity(): bool {
        return ! is_null( $this->connectorEntity );
    }
    
    public function getConnectionName(): string {
        if ( empty( $this->entities ) ) {
            throw new \InvalidArgumentException( 'Cannot create connection name when no entities are registered to the connection' );
        }
        
        $names = array_map( static fn( ClassMetaData $classMetaData ): string => $classMetaData->getEntity()->getClassName(), $this->entities );
        
        return self::namesToConnectionName( $names );
    }
    
    public static function namesToConnectionName( array $names ): string {
        sort( $names, SORT_STRING );
        
        return EntityMappingCache::serializeClassName( implode( '___', $names ) );
    }
    
}