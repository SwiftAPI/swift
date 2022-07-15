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
use Cycle\ORM\Mapper\Mapper;
use Cycle\ORM\Mapper\StdMapper;
use Cycle\ORM\Relation;
use Cycle\Schema\Definition\Map\OptionMap;
use Cycle\Schema\Registry;
use Cycle\Schema\Relation\RelationSchema;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\Rules\English\InflectorFactory;
use Swift\DependencyInjection\ServiceLocator;
use Swift\Orm\Attributes\Behavior\EventListener;
use Swift\Orm\Attributes\Embeddable;
use Swift\Orm\Attributes\Relation\Embedded;
use Swift\Orm\Attributes\Relation\RelationFieldInterface;
use Swift\Orm\Behavior\SchemaModifierInterface;
use Cycle\Schema\Definition\Entity as EntitySchema;
use Swift\Orm\DependencyInjection\OrmDiTags;
use Swift\Orm\Mapping\Definition\Entity;
use Swift\Orm\Mapping\Definition\IndexType;
use Swift\Orm\Types\FieldTypes;
use Swift\Orm\Types\Typecast;

class Configurator {
    
    private array $lifeCycles = [];
    
    private Inflector $inflector;
    
    public function __construct(
        protected readonly \Swift\Orm\Mapping\ClassMetaDataFactory    $classMetaDataFactory,
        protected readonly \Swift\Orm\Mapping\NamingStrategyInterface $namingStrategy,
        protected readonly \Swift\Orm\Mapping\Driver\AttributeReader  $reader,
        protected readonly int                                        $tableNamingStrategy = \Cycle\Annotated\Entities::TABLE_NAMING_PLURAL,
    ) {
        $this->inflector = ( new InflectorFactory() )->build();
        foreach ( ( new ServiceLocator() )->getServicesByTag( OrmDiTags::ORM_LIFECYCLE->value ) as $lifeCycle ) {
            if ( ! array_key_exists( $lifeCycle::getEntityClass(), $this->lifeCycles ) ) {
                $this->lifeCycles[ $lifeCycle::getEntityClass() ] = [];
            }
            
            $this->lifeCycles[ $lifeCycle::getEntityClass() ][] = $lifeCycle;
        }
    }
    
    public function initEntity( \Swift\Orm\Mapping\Definition\Entity $entityDefinition ): \Cycle\Schema\Definition\Entity {
        $definition = new \Cycle\Schema\Definition\Entity();
        $definition->setRole( $entityDefinition->getDatabaseName() );
        $definition->setClass( $entityDefinition->getClassName() );
        $definition->setDatabase( 'default' );
        $definition->setMapper( Mapper::class );
        $definition->setTableName( $entityDefinition->getDatabaseName() );
        
        return $definition;
    }
    
    public function initFields( \Swift\Orm\Mapping\Definition\Entity $entityDefinition, \Cycle\Schema\Definition\Entity $definition ): void {
        foreach ( $entityDefinition->getFields() as $field ) {
            if ( $field->isHidden() ) {
                continue;
            }
            
            $fieldDefinition = new \Cycle\Schema\Definition\Field();
            
            $fieldDefinition->setColumn( $field->getDatabaseName() );
            
            $fieldDefinition->setType( $field->getType()->getDatabaseType() );
            $fieldDefinition->setTypecast( $field->getType()->getName() );
            
            if ( $entityDefinition->getPrimaryKey() === $field ) {
                $fieldDefinition->setType( 'primary' );
                $fieldDefinition->setPrimary( true );
            }
            
            if ( strtolower( $field->getType()->getName() ) === FieldTypes::UUID->value ) {
                $fieldDefinition->setType( 'uuid' );
                $fieldDefinition->setTypecast( null );
            }
            
            if ( $field->getType()->getName() === FieldTypes::ENUM->value ) {
                $fieldDefinition->setType( sprintf( 'enum(%s)', implode( ',', $this->extraEnumValues( $field->getEnum() ) ) ) );
                $fieldDefinition->setTypecast( 'enum' );
            }
            
            $fieldDefinitionOptions = $fieldDefinition->getOptions();
            if ( $field->isNullable() ) {
                $this->setOptionToOptionSet( Relation::NULLABLE, $field->isNullable(), $fieldDefinitionOptions );
            }
            
            $definition->getFields()->set( $field->getPropertyName(), $fieldDefinition );
        }
    }
    
    protected function extraEnumValues( string $enum ): array {
        $values = [];
        foreach ( $enum::cases() as $case ) {
            $values[] = $case?->value ?? $case->name;
        }
        
        return $values;
    }
    
    public function initFieldsFromReflection( \ReflectionClass $class, \Cycle\Schema\Definition\Entity $definition ): void {
        $entityDefinition = new Entity(
            $class->getName(),
            $class->getName(),
            'none',
            null,
        );
        foreach ( $class->getProperties() as $property ) {
            $field = $this->classMetaDataFactory->createField( $property );
            if ( ! $field ) {
                continue;
            }
            $entityDefinition->addField( $field );
        }
        
        if ( count( $entityDefinition->getFields() ) < 1 ) {
            return;
        }
        
        $this->initFields( $entityDefinition, $definition );
    }
    
    public function initRelations( \Cycle\Schema\Definition\Entity $entity, \ReflectionClass $class, Registry $registry ): void {
        $reader = new \Spiral\Attributes\Internal\NativeAttributeReader();
        
        foreach ( $class->getProperties() as $property ) {
            try {
                $metaData = new \AppendIterator();
                $metaData->append( new \NoRewindIterator( $reader->getPropertyMetadata( $property, RelationFieldInterface::class ) ) );
                //$metaData->append( new \NoRewindIterator( $reader->getPropertyMetadata( $property, RelationInterface::class ) ) );
            } catch ( \Exception $e ) {
                throw new AnnotationException( $e->getMessage(), $e->getCode(), $e );
            }
            
            foreach ( $metaData as $meta ) {
                assert( ( $meta instanceof \Swift\Orm\Attributes\Relation\RelationFieldInterface ) || ( $meta instanceof \Swift\Orm\Attributes\Relation\RelationInterface ) );
                
                if ( $meta->getTargetEntity() === null ) {
                    throw new AnnotationException(
                        "Relation target definition is required on `{$entity->getClass()}`.`{$property->getName()}`"
                    );
                }
                
                $relation = new \Cycle\Schema\Definition\Relation();
                $relation->setTarget( $this->resolveName( $meta->getTargetEntity(), $class ) );
                $relation->setType( $this->convertRelationEnum( $meta->getRelationType() ) );
                
                $relationDefinitionOptions = $relation->getOptions();
                
                $this->setOptionToOptionSet( \Cycle\ORM\Relation::CASCADE, true, $relationDefinitionOptions );
                $this->setOptionToOptionSet( \Cycle\ORM\Relation::NULLABLE, $meta->isNullable(), $relationDefinitionOptions );
                
                if ( $meta instanceof \Swift\Orm\Attributes\Relation\ManyToMany ) {
                    $relation->setTarget( $this->classMetaDataFactory->getClassMetaData( $meta->getTargetEntity() )->getEntity()->getDatabaseName() );
                    $connectionRelationDefinition = $this->generateThroughEntity( $class->getName(), $meta, $registry );
                    $this->setOptionToOptionSet( \Cycle\ORM\Relation::THROUGH_ENTITY, $connectionRelationDefinition->getRole(), $relationDefinitionOptions );
                }
                
                $inverse = $meta->getInverse();
                if ( $inverse !== null ) {
                    $relation->setInverse(
                        $inverse->getAs(),
                        $this->convertRelationEnum( $inverse->getRelationType() ),
                        Relation::LOAD_EAGER,
                    );
                }
                
                
                if ( $meta instanceof Embedded && $meta->getPrefix() === null ) {
                    /** @var Embeddable|null $embeddable */
                    $embeddable = $this->reader->getEmbeddableAttribute( $relation->getTarget() );
                    $meta->setPrefix( $embeddable->getColumnPrefix() );
                }
                
                if ( $meta instanceof Embedded ) {
                    $this->setOptionToOptionSet( RelationSchema::EMBEDDED_PREFIX, $meta->getPrefix(), $relationDefinitionOptions );
                }

//            foreach ( $meta->getOptions() as $option => $value ) {
//                $value = match ( $option ) {
//                    'collection' => $this->resolveName( $value, $class ),
//                    'though', 'through' => $this->resolveName( $value, $class ),
//                    default => $value
//                };
//
//                $relation->getOptions()->set( $option, $value );
//            }
                
                // need relation definition
                $entity->getRelations()->set( $property->getName(), $relation );
            }
        }
    }

//    public function initRelations( \Cycle\Schema\Definition\Entity $entity, \ReflectionClass $class, Registry $registry ): void {
//        $reader = new \Spiral\Attributes\Internal\NativeAttributeReader();
//
//        foreach ( $class->getProperties() as $property ) {
//            try {
//                $meta = $this->reader->getRelationAttribute( $property );
//                $metaData = $reader->getPropertyMetadata( $property, RelationInterface::class );
//            } catch ( \Exception $e ) {
//                throw new AnnotationException( $e->getMessage(), $e->getCode(), $e );
//            }
//
//            if ( ! $meta ) {
//                continue;
//            }
//
//            assert( ( $meta instanceof \Swift\Orm\Attributes\Relation\RelationFieldInterface ) || ( $meta instanceof \Swift\Orm\Attributes\Relation\RelationInterface ) );
//
//            if ( $meta->getTargetEntity() === null ) {
//                throw new AnnotationException(
//                    "Relation target definition is required on `{$entity->getClass()}`.`{$property->getName()}`"
//                );
//            }
//
//            $relation = new \Cycle\Schema\Definition\Relation();
//            $relation->setTarget( $this->resolveName( $meta->getTargetEntity(), $class ) );
//            $relation->setType( $this->convertRelationEnum( $meta->getRelationType() ) );
//
//            $relationDefinitionOptions = $relation->getOptions();
//
//            $this->setOptionToOptionSet( \Cycle\ORM\Relation::CASCADE, true, $relationDefinitionOptions );
//            $this->setOptionToOptionSet( \Cycle\ORM\Relation::NULLABLE, $meta->isNullable(), $relationDefinitionOptions );
//
//            if ( $meta instanceof \Swift\Orm\Attributes\Relation\ManyToMany ) {
//                $relation->setTarget( $this->classMetaDataFactory->getClassMetaData( $meta->getTargetEntity() )->getEntity()->getDatabaseName() );
//                $connectionRelationDefinition = $this->generateThroughEntity( $class->getName(), $meta, $registry );
//                $this->setOptionToOptionSet( \Cycle\ORM\Relation::THROUGH_ENTITY, $connectionRelationDefinition->getRole(), $relationDefinitionOptions );
//            }
//
//            $inverse = $meta->getInverse();
//            if ( $inverse !== null ) {
//                $relation->setInverse(
//                    $inverse->getAs(),
//                    $this->convertRelationEnum( $inverse->getRelationType() ),
//                    Relation::LOAD_EAGER,
//                );
//            }
//
//
//            if ( $meta instanceof Embedded && $meta->getPrefix() === null ) {
//                /** @var Embeddable|null $embeddable */
//                $embeddable = $this->reader->getEmbeddableAttribute( $relation->getTarget() );
//                $meta->setPrefix( $embeddable->getColumnPrefix() );
//            }
//
//            if ( $meta instanceof Embedded ) {
//                $this->setOptionToOptionSet( RelationSchema::EMBEDDED_PREFIX, $meta->getPrefix(), $relationDefinitionOptions );
//            }
//
////            foreach ( $meta->getOptions() as $option => $value ) {
////                $value = match ( $option ) {
////                    'collection' => $this->resolveName( $value, $class ),
////                    'though', 'through' => $this->resolveName( $value, $class ),
////                    default => $value
////                };
////
////                $relation->getOptions()->set( $option, $value );
////            }
//
//            // need relation definition
//            $entity->getRelations()->set( $property->getName(), $relation );
//        }
//    }
    
    protected function generateThroughEntity( string $entity, \Swift\Orm\Attributes\Relation\ManyToMany $relation, \Cycle\Schema\Registry $registry ): \Cycle\Schema\Definition\Entity {
        $definition = new \Cycle\Schema\Definition\Entity();
        
        $dbName = $this->namingStrategy->getEntitiesConnectionEntityName(
            [ $this->classMetaDataFactory->getClassMetaData( $entity )->getEntity(), $this->classMetaDataFactory->getClassMetaData( $relation->getTargetEntity() )->getEntity() ],
            [],
        );
        
        $definition->setRole( $dbName );
        $definition->setDatabase( 'default' );
        $definition->setMapper( StdMapper::class );
        $definition->setTableName( $dbName );
        $definition->setTypecast( Typecast::class );
        
        $fieldDefinition = new \Cycle\Schema\Definition\Field();
        $fieldDefinition->setColumn( 'id' );
        $fieldDefinition->setType( 'primary' )->setPrimary( true );
        
        $definition->getFields()->set( 'id', $fieldDefinition );
        
        $registry->register( $definition )->linkTable( $definition, 'default', $dbName );
        
        return $definition;
    }
    
    /**
     * Resolve class or role name relative to the current class.
     */
    public function resolveName( ?string $name, \ReflectionClass $class ): ?string {
        if ( $name === null || class_exists( $name, true ) || interface_exists( $name, true ) ) {
            return $name;
        }
        
        $resolved = sprintf(
            '%s\\%s',
            $class->getNamespaceName(),
            ltrim( str_replace( '/', '\\', $name ), '\\' )
        );
        
        if ( class_exists( $resolved, true ) || interface_exists( $resolved, true ) ) {
            return ltrim( $resolved, '\\' );
        }
        
        return $name;
    }
    
    
    public function initModifiers( EntitySchema $entity, \ReflectionClass $class ): void {
        $reader = new \Spiral\Attributes\Internal\NativeAttributeReader();
        try {
            $metadata = $reader->getClassMetadata( $class, SchemaModifierInterface::class );
        } catch ( \Exception $e ) {
            throw new AnnotationException( $e->getMessage(), $e->getCode(), $e );
        }
        
        foreach ( $metadata as $meta ) {
            assert( $meta instanceof SchemaModifierInterface );
            
            // need relation definition
            $entity->addSchemaModifier( $meta );
        }
        
        // Add lifecycles as event listeners on entity
        if ( array_key_exists( $entity->getClass(), $this->lifeCycles ) ) {
            foreach ( $this->lifeCycles[ $entity->getClass() ] as $lifeCycle ) {
                $entity->addSchemaModifier( ( new EventListener( $lifeCycle, [] ) )->withRole( $entity->getRole() ) );
            }
        }
    }
    
    public function initEmbedding( Embeddable $emb, \ReflectionClass $class ): EntitySchema {
        $e = new EntitySchema();
        $e->setClass( $class->getName() );
        
        $e->setRole( $emb->getRole() ?? $this->inflector->camelize( $class->getShortName() ) );
        
        // representing classes
        $e->setMapper( $this->resolveName( $emb->getMapper(), $class ) );
        
        return $e;
    }
    
    public function initIndexes( \Cycle\Schema\Registry $registry, \Swift\Orm\Mapping\Definition\Entity $definition, EntitySchema $entity ): void {
        foreach ( $definition->getIndexes() as $index ) {
            $table = $registry->getTableSchema( $entity );
            if ( $index->getIndexType() === IndexType::PRIMARY ) {
                $table->setPrimaryKeys( $this->mapColumns( $index->getFields() ) );
                $entity->setPrimaryColumns( $this->mapColumns( $index->getFields() ) );
                continue;
            }
            
            $indexDef = $table->index( $this->mapColumns( $index->getFields() ) );
            
            if ( $index->getIndexType() === IndexType::UNIQUE ) {
                $indexDef->unique();
            }
        }
    }
    
    /**
     * @param \Swift\Orm\Mapping\Definition\Field[] $fields
     *
     * @return string[]
     */
    public function mapColumns( array $fields ): array {
        return array_map( static fn( \Swift\Orm\Mapping\Definition\Field $field ) => $field->getDatabaseName(), $fields );
    }
    
    protected function setOptionToOptionSet( int $option, mixed $value, OptionMap $optionMap ): void {
        $optionName = array_search( $option, Entities::OPTION_MAP, true );
        if ( ! $optionName ) {
            throw new \InvalidArgumentException( sprintf( '%s is not a valid option', $option ) );
        }
        
        $optionMap->set( (string) $optionName, $value );
    }
    
    public function convertRelationEnum( \Swift\Orm\Mapping\Definition\Relation\EntityRelationType $type ): string {
        return match ( $type ) {
            \Swift\Orm\Mapping\Definition\Relation\EntityRelationType::REFERS_TO => 'refersTo',
            \Swift\Orm\Mapping\Definition\Relation\EntityRelationType::BELONGS_TO => 'belongsTo',
            \Swift\Orm\Mapping\Definition\Relation\EntityRelationType::HAS_MANY => 'hasMany',
            \Swift\Orm\Mapping\Definition\Relation\EntityRelationType::HAS_ONE => 'hasOne',
            \Swift\Orm\Mapping\Definition\Relation\EntityRelationType::MANY_TO_MANY => 'manyToMany',
            \Swift\Orm\Mapping\Definition\Relation\EntityRelationType::EMBEDDED => 'embedded',
            default => throw new \InvalidArgumentException( sprintf( 'Could not relation type: %s', $type->value ) ),
        };
    }
    
}