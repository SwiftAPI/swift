<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Attributes\Relation;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Swift\DependencyInjection\Attributes\DI;
use Swift\Orm\Entity\EntityInterface;
use Swift\Orm\Mapping\Definition\Relation\EntityRelationType;

/**
 * Connect One to BelongsTo relationship between entities
 *
 * Almost the same as HasOne Relationship, but determines the entity is 'owned' by the referenced entity.
 * Will also automatically load on related entity
 *
 * Class BelongsTo
 * @package Swift\Orm\Attributes
 */
#[\Attribute( \Attribute::TARGET_PROPERTY ), DI( autowire: false )]
#[NamedArgumentConstructor]
#[\AllowDynamicProperties]
final class BelongsTo implements RelationFieldInterface {
    
    private readonly EntityRelationType $entityRelationType;
    private readonly Inverse $inverse;
    
    /**
     * OneToOne constructor.
     *
     * @param string $targetEntity Entity to join
     */
    public function __construct(
        private readonly string $targetEntity,
        private readonly string $inverseAs,
        private readonly bool   $nullable = false,
    ) {
        if ( ! is_a( $this->targetEntity, EntityInterface::class, true ) ) {
            throw new \InvalidArgumentException( sprintf( 'Cannot use %s as and Entity relation as this class does not implement %s', $this->targetEntity, EntityInterface::class ) );
        }
        $this->entityRelationType = EntityRelationType::BELONGS_TO;
        $this->inverse            = new Inverse( $this->inverseAs, EntityRelationType::HAS_MANY );
    }
    
    public function getRelationType(): EntityRelationType {
        return $this->entityRelationType;
    }
    
    public function getTargetEntity(): string {
        return $this->targetEntity;
    }
    
    public function getInverse(): Inverse {
        return $this->inverse;
    }
    
    public function toObject(): \stdClass {
        $object = new \stdClass();
        foreach ( get_object_vars( $this ) as $name => $var ) {
            $object->{$name} = $var;
        }
        
        return $object;
    }
    
    public function isNullable(): bool {
        return $this->nullable;
    }
    
}