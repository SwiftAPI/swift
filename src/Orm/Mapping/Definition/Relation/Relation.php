<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping\Definition\Relation;


use Swift\Orm\Mapping\Definition\Entity;

abstract class Relation {
    
    /**
     * @param string                                                    $name
     * @param \Swift\Orm\Mapping\Definition\Relation\EntityRelationType $entityRelationType
     * @param string                                                    $targetEntity
     * @param string|null                                               $targetEntityField
     * @param string                                                    $currentEntityField
     * @param bool                                                      $isOwningSide
     * @param \Swift\Orm\Mapping\Definition\Entity|null                 $entity
     */
    public function __construct(
        private readonly string             $name,
        private readonly EntityRelationType $entityRelationType,
        private readonly string             $targetEntity,
        private readonly ?string            $targetEntityField,
        private readonly string             $currentEntityField,
        private readonly bool               $isOwningSide,
        private ?Entity                     $entity = null,
    ) {
    }
    
    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }
    
    /**
     * @return \Swift\Orm\Mapping\Definition\Relation\EntityRelationType
     */
    public function getEntityRelationType(): EntityRelationType {
        return $this->entityRelationType;
    }
    
    /**
     * @return string
     */
    public function getTargetEntity(): string {
        return $this->targetEntity;
    }
    
    /**
     * @return string
     */
    public function getTargetEntityField(): string {
        return $this->targetEntityField;
    }
    
    /**
     * @return string
     */
    public function getCurrentEntityField(): string {
        return $this->currentEntityField;
    }
    
    /**
     * @return bool
     */
    public function isOwningSide(): bool {
        return $this->isOwningSide;
    }
    
    /**
     * @return \Swift\Orm\Mapping\Definition\Entity|null
     */
    public function getEntity(): ?Entity {
        return $this->entity;
    }
    
    /**
     * @param \Swift\Orm\Mapping\Definition\Entity|null $entity
     */
    public function setEntity( ?Entity $entity ): void {
        $this->entity = $entity;
    }
    
    public static function createRelation(
        string $name,
        EntityRelationType $entityRelationType,
        string $targetEntity,
        ?string $targetEntityField,
        string $currentEntityField,
        bool $isOwner = false,
        ?Entity $entity = null,
    ): ?Relation {
        return match ($entityRelationType) {
            EntityRelationType::HAS_ONE => new HasOneRelation( $name, $entityRelationType, $targetEntity, $targetEntityField, $currentEntityField, $isOwner, $entity ),
            EntityRelationType::HAS_MANY => new HasManyRelation( $name, $entityRelationType, $targetEntity, $targetEntityField, $currentEntityField, $isOwner, $entity ),
            EntityRelationType::BELONGS_TO => new BelongsToRelation( $name, $entityRelationType, $targetEntity, $targetEntityField, $currentEntityField, $isOwner, $entity ),
            EntityRelationType::MANY_TO_MANY => new ManyToManyRelation( $name, $entityRelationType, $targetEntity, $targetEntityField, $currentEntityField, $isOwner, $entity ),
            default => null,
        };
    }
    
    
}