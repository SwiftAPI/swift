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
 * Connect Many-to-Many relationship between entities
 *
 * Class ManyToOne
 * @package Swift\Orm\Attributes
 */
#[\Attribute( \Attribute::TARGET_PROPERTY ), DI( autowire: false )]
#[NamedArgumentConstructor]
final class ManyToMany implements RelationFieldInterface {
    
    private readonly EntityRelationType $entityRelationType;
    
    /**
     * ManyToMany constructor.
     *
     * @param string $targetEntity Entity to join
     */
    public function __construct(
        private readonly string $targetEntity,
    ) {
        if ( ! is_a( $this->targetEntity, EntityInterface::class, true ) ) {
            throw new \InvalidArgumentException( sprintf( 'Cannot use %s as and Entity relation as this class does not implement %s', $this->targetEntity, EntityInterface::class ) );
        }
        $this->entityRelationType = EntityRelationType::MANY_TO_MANY;
    }
    
    public function getRelationType(): EntityRelationType {
        return $this->entityRelationType;
    }
    
    public function getTargetEntity(): string {
        return $this->targetEntity;
    }
    
    public function getInverse(): ?Inverse {
        // @TODO: Support inversification for many-to-many relations
        return null;
    }
    
    public function toObject(): \stdClass {
        $object = new \stdClass();
        foreach ( get_object_vars( $this ) as $name => $var ) {
            $object->{$name} = $var;
        }
        
        return $object;
    }
    
    public function isNullable(): bool {
        return false;
    }
    
}