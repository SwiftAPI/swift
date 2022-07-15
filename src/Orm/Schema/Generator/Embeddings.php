<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Schema\Generator;


use Cycle\Annotated\Exception\AnnotationException;
use Cycle\Schema\Definition\Entity as EntitySchema;
use Cycle\Schema\GeneratorInterface;
use Cycle\Schema\Registry;
use Swift\Orm\Attributes\Embeddable;
use Swift\Orm\Attributes\Relation\RelationInterface;

/**
 * Generates ORM schema based on annotated classes.
 */
final class Embeddings implements GeneratorInterface {
    
    protected readonly \Swift\Orm\Schema\Generator\Configurator $configurator;
    
    public function __construct(
        protected readonly \Swift\Orm\Mapping\ClassLocator            $classLocator,
        protected readonly \Swift\Orm\Mapping\ClassMetaDataFactory    $classMetaDataFactory,
        protected readonly \Swift\Orm\Mapping\NamingStrategyInterface $namingStrategy,
        protected readonly \Swift\Orm\Mapping\Driver\AttributeReader  $reader,
        protected readonly int                                        $tableNamingStrategy = \Cycle\Annotated\Entities::TABLE_NAMING_PLURAL,
    ) {
        $this->configurator = new \Swift\Orm\Schema\Generator\Configurator( $this->classMetaDataFactory, $this->namingStrategy, $this->reader, $this->tableNamingStrategy );
    }
    
    public function run( Registry $registry ): Registry {
        $reader = new \Spiral\Attributes\Internal\NativeAttributeReader();
        
        foreach ( $this->classLocator->getClasses() as $class ) {
            try {
                $em = $reader->firstClassMetadata( $class, Embeddable::class );
            } catch ( \Exception $e ) {
                throw new AnnotationException( $e->getMessage(), $e->getCode(), $e );
            }
            if ( $em === null ) {
                continue;
            }
            
            \assert( $em instanceof Embeddable );
            
            $e = $this->configurator->initEmbedding( $em, $class );
            
            $this->verifyNoRelations( $e, $class );
            
            // columns
            $this->configurator->initFieldsFromReflection( $class, $e );
            
            // register entity (OR find parent)
            $registry->register( $e );
        }
        
        return $registry;
    }
    
    public function verifyNoRelations( EntitySchema $entity, \ReflectionClass $class ): void {
        $reader = new \Spiral\Attributes\Internal\NativeAttributeReader();
        
        foreach ( $class->getProperties() as $property ) {
            try {
                $ann = $reader->getPropertyMetadata( $property );
            } catch ( \Exception $e ) {
                throw new AnnotationException( $e->getMessage(), $e->getCode(), $e );
            }
            
            foreach ( $ann as $ra ) {
                if ( $ra instanceof RelationInterface ) {
                    throw new AnnotationException(
                        "Relations are not allowed within embeddable entities in `{$entity->getClass()}`"
                    );
                }
            }
        }
    }
}
