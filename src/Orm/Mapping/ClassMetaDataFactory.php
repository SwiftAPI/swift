<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping;

use Psr\Cache\CacheItemInterface;
use Swift\Code\ReflectionFactory;
use Swift\Configuration\ConfigurationInterface;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Kernel\KernelDiTags;
use Swift\Orm\Entity\EntityInterface;
use Swift\Orm\Mapping\Definition\Entity;
use Swift\Orm\Mapping\Definition\Field;
use Swift\Orm\Mapping\Definition\Index;
use Swift\Orm\Mapping\Definition\IndexType;
use Swift\Orm\Mapping\Definition\Relation\Relation;
use Swift\Orm\Mapping\Definition\Relation\RelationInterface;
use Swift\Orm\Mapping\Driver\AttributeReader;
use Swift\Orm\Types\TypeTransformer;

/**
 * Class ClassMetaDataFactory
 * @package Swift\Orm\Mapping
 */
#[Autowire]
class ClassMetaDataFactory {
    
    protected bool $isCompiled = false;
    /** @var EntityInterface[] */
    protected array $entities = [];
    /** @var \Swift\Orm\Mapping\MetaDataFactoryInterface[] $metaDataFactories */
    protected array $metaDataFactories;
    
    
    /**
     * ClassMetaDataFactory constructor.
     */
    public function __construct(
        protected readonly RegistryInterface       $registry,
        protected readonly ReflectionFactory       $reflectionFactory,
        protected readonly AttributeReader         $attributeReader,
        protected readonly ConfigurationInterface  $configuration,
        protected readonly NamingStrategyInterface $entityMappingNamingStrategy,
        protected readonly TypeTransformer         $typeTransformer,
    ) {
    }
    
    /**
     * @return \Swift\Orm\Mapping\ClassMetaData[]
     */
    public function getAllClassMetaData(): array {
        if ( ! $this->isCompiled ) {
            $this->compile();
        }
        
        $items = [];
        
        foreach ( $this->entities as $entity => $entityInstance ) {
            $item = $this->getClassMetaData( $entity );
            
            if ( $item ) {
                $items[] = $item;
            }
        }
        
        return $items;
    }
    
    protected function compile(): void {
        $this->isCompiled = true;
        
        $cacheLoaded = false;
        
        // Pre populate cache
        foreach ( $this->entities as $entity => $entityInstance ) {
            $cache = $this->getFromCache( $entity );
            
            if ( $cache->isHit() ) {
                $cacheLoaded = true;
                continue;
            }
            
            // Validate provided class
            if ( ! class_exists( $entity ) ) {
                throw new \InvalidArgumentException( sprintf( '%s is not found as a class', $entity ) );
            }
            if ( ! is_a( $entity, EntityInterface::class, true ) ) {
                throw new \InvalidArgumentException( sprintf( '%s expected, but got %s', EntityInterface::class, $entity ) );
            }
            
            $reflection      = $this->reflectionFactory->getReflectionClass( $entity );
            $entityAttribute = $this->attributeReader->getEntityAttribute( $entity );
            $entity          = new Entity(
                $reflection->getName(),
                $entityAttribute->getTableName(),
                $this->configuration->get( 'connection.prefix', 'database' ),
                $entityAttribute->getTableComment(),
            );
            
            foreach ( $reflection->getProperties() as $property ) {
                $field = $this->createField( $property );
                if ( $field ) {
                    $entity->addField( $field );
                }
            }
            
            // Compile the entity based on the fields
            foreach ( $entity->getFields() as $field ) {
                if ( $field->getIndex() ) {
                    $entity->addIndex(
                        new Index(
                            $this->entityMappingNamingStrategy->getIndexName( $entity, [ $field ], $field->getIndex() ),
                            $field->getIndex(),
                            [ $field ],
                        )
                    );
                }
                
                if ( $field->getIndex() === IndexType::PRIMARY ) {
                    $entity->setPrimaryKey( $field );
                }
            }
            
            foreach ( $this->attributeReader->getIndexAttributes( $reflection->getName() ) ?? [] as $indexAttribute ) {
                $fields = [];
                foreach ( $indexAttribute->getFields() as $field ) {
                    $fieldItem = $entity->getFieldByPropertyName( $field );
                    if ( $fieldItem ) {
                        $fields[] = $fieldItem;
                    }
                }
                $entity->addIndex(
                    new Index(
                        $this->entityMappingNamingStrategy->getIndexName( $entity, $fields, $indexAttribute->getType() ),
                        $indexAttribute->getType(),
                        $fields,
                    )
                );
            }
            
            $classMeta = new ClassMetaData( $entity, $reflection );
            
            $cache->set( $classMeta );
            $this->registry->saveClassMetaDataCacheItem( $cache );
        }

//        foreach ( $this->entities as $entity => $entityInstance ) {
//            $cache = $this->getFromCache( $entity );
//
//            if ( $cacheLoaded || ! $cache->isHit() ) {
//                continue;
//            }
//
//            /** @var \Swift\Orm\Mapping\ClassMetaData $data */
//            $data = $cache->get();
//
//            foreach ( $this->metaDataFactories as $metaDataFactory ) {
//                if ($metaDataFactory->supports( $data, $this->registry )) {
//                    $data = $metaDataFactory->create( $data, $this->registry );
//                }
//            }
//
//            $cache->set( $data );
//            $this->registry->saveClassMetaDataCacheItem( $cache );
//        }
        
    }
    
    protected function getFromCache( string $name ): CacheItemInterface {
        return $this->registry->getClassMetaDataCacheItem( $name );
    }
    
    public function createField( \ReflectionProperty $property ): ?Field {
        $fieldAttribute = $this->attributeReader->getFieldAttribute( $property );
        if ( ! $fieldAttribute ) {
            return null;
        }
        
        $index = IndexType::getIndexTypeForFieldAttribute( $fieldAttribute );
        
        return new Field(
            $property->getName(),
            $fieldAttribute->getName(),
            $fieldAttribute,
            $this->typeTransformer->getType( $fieldAttribute->getType() ),
            $property,
            $fieldAttribute->getSerialize(),
            $fieldAttribute->getEnum(),
            $index,
        );
    }
    
    protected function createRelation( \ReflectionProperty $property ): ?Relation {
        $relationAttribute = $this->attributeReader->getRelationAttribute( $property );
        
        if ( ! $relationAttribute ) {
            return null;
        }
        
        return Relation::createRelation(
            $property->getName(),
            $relationAttribute->getRelationType(),
            $relationAttribute->getJoiningEntity(),
            $relationAttribute->getJoiningEntityField(),
            $relationAttribute->getCurrentEntityField(),
        );
    }
    
    public function getClassMetaData( string $entity ): ?ClassMetaData {
        if ( ! $this->isCompiled ) {
            $this->compile();
        }
        
        if ( empty( $entity ) ) {
            return null;
        }
        
        $cache = $this->getFromCache( $entity );
        
        return $cache->isHit() ? $cache->get() : null;
    }
    
    #[Autowire]
    public function setEntities( #[Autowire( tag: KernelDiTags::ENTITY )] ?iterable $entities ): void {
        if ( ! $entities ) {
            return;
        }
        
        $entities = iterator_to_array( $entities );
        
        foreach ( $entities as $entity ) {
            $this->entities[ $entity::class ] = $entity;
        }
    }
    
    #[Autowire]
    public function setMetaDataFactories( #[Autowire( tag: 'orm.metadata_factory' )] ?iterable $metaDataFactories ): void {
        if ( ! $metaDataFactories ) {
            return;
        }
        
        $metaDataFactories = iterator_to_array( $metaDataFactories );
        
        foreach ( $metaDataFactories as $metaDataFactory ) {
            $this->metaDataFactories[ $metaDataFactory::class ] = $metaDataFactory;
        }
    }
    
    
}