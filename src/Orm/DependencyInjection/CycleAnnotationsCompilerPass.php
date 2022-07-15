<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\DependencyInjection;

use Cycle\Annotated\Annotation\Embeddable;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Inheritance\DiscriminatorColumn;
use Cycle\Annotated\Annotation\Inheritance\JoinedTable;
use Cycle\Annotated\Annotation\Inheritance\SingleTable;
use Cycle\Annotated\Annotation\Table;
use Cycle\ORM\Entity\Behavior\EventListener;
use Cycle\ORM\Entity\Behavior\Hook;
use Cycle\ORM\Entity\Behavior\OptimisticLock;
use Swift\DependencyInjection\CompilerPass\CompilerPassInterface;
use Swift\Orm\Behavior\SchemaModifierInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class CycleAnnotationsCompilerPass
 * @package Swift\Orm\DependencyInjection
 *
 * Bridge to register Cycle attributes to container by tagging them
 */
class CycleAnnotationsCompilerPass implements CompilerPassInterface {
    
    /**
     * @inheritDoc
     */
    public function process( ContainerBuilder $container ): void {
        foreach ( $container->getDefinitions() as $definition ) {
            $classReflection = $container->getReflectionClass( $definition->getClass() );
            
            $hasAnyMatch = false;
            if ( ! empty( $classReflection?->getAttributes( name: Table::class ) ) ) {
                $definition->addTag( name: OrmDiTags::ORM_TABLE->value );
                $hasAnyMatch = true;
            }
            if ( ! empty( $classReflection?->getAttributes( name: SingleTable::class ) ) ) {
                $definition->addTag( name: OrmDiTags::ORM_TABLE->value );
                $hasAnyMatch = true;
            }
            if ( ! empty( $classReflection?->getAttributes( name: JoinedTable::class ) ) ) {
                $definition->addTag( name: OrmDiTags::ORM_TABLE->value );
                $hasAnyMatch = true;
            }
            if ( ! empty( $classReflection?->getAttributes( name: DiscriminatorColumn::class ) ) ) {
                $definition->addTag( name: OrmDiTags::ORM_TABLE->value );
                $hasAnyMatch = true;
            }
            if ( ! empty( $classReflection?->getAttributes( name: Entity::class ) ) ) {
                $definition->addTag( name: OrmDiTags::ORM_ENTITY->value );
                $hasAnyMatch = true;
            }
            if ( ! empty( $classReflection?->getAttributes( name: Embeddable::class ) ) ) {
                $definition->addTag( name: OrmDiTags::ORM_EMBEDDABLE->value );
                $hasAnyMatch = true;
            }
            if ( ! empty( $classReflection?->getAttributes( name: Hook::class ) ) ) {
                $definition->addTag( name: OrmDiTags::ORM_HOOK->value );
                $hasAnyMatch = true;
            }
            if ( ! empty( $classReflection?->getAttributes( name: EventListener::class ) ) ) {
                $definition->addTag( name: OrmDiTags::ORM_EVENTLISTENER->value );
                $hasAnyMatch = true;
            }
            if ( ! empty( $classReflection?->getAttributes( name: OptimisticLock::class ) ) ) {
                $definition->addTag( name: OrmDiTags::ORM_OPTIMISTIC_LOCK->value );
                $hasAnyMatch = true;
            }
            
            if ( is_a( $classReflection?->getName(), SchemaModifierInterface::class, true ) ) {
                $hasAnyMatch = true;
            }
    
            if ( ! empty( $classReflection?->getAttributes( name: \Swift\Orm\Attributes\Embeddable::class ) ) ) {
                $definition->addTag( name: OrmDiTags::ORM_EMBEDDABLE->value );
                $hasAnyMatch = true;
            }
            
            if ( $hasAnyMatch ) {
                $definition->addTag( name: OrmDiTags::ORM_ANNOTATED->value );
            }
        }
    }
}