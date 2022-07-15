<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping\Factory\Relations;


use Swift\Code\ReflectionFactory;
use Swift\Configuration\ConfigurationInterface;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Orm\Cache\EntityMappingCache;
use Swift\Orm\Mapping\Definition\Relation\EntitiesConnection;
use Swift\Orm\Mapping\Definition\Relation\Relation;
use Swift\Orm\Mapping\Driver\AttributeReader;
use Swift\Orm\Mapping\ClassMetaData;
use Swift\Orm\Mapping\NamingStrategyInterface;

#[Autowire]
abstract class AbstractRelationMetaDataFactory implements RelationMetaDataFactoryInterface {
    
    public function __construct(
        protected EntityMappingCache      $cache,
        protected ReflectionFactory       $reflectionFactory,
        protected AttributeReader         $attributeReader,
        protected ConfigurationInterface  $configuration,
        protected NamingStrategyInterface $entityMappingNamingStrategy,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    abstract public function supports( \ReflectionProperty $property, ClassMetaData $classMetadata, \Swift\Orm\Mapping\RegistryInterface $registry ): bool;
    
    abstract public function createRelationMetaData( \ReflectionProperty $property, ClassMetaData $classMetaData, \Swift\Orm\Mapping\RegistryInterface $registry ): void;
    
    protected function createRelation( \ReflectionProperty $property, ClassMetaData $classMetaData, \Swift\Orm\Mapping\RegistryInterface $registry ): ?Relation {
        $relationAttribute = $this->attributeReader->getRelationAttribute( $property );
        
        if ( ! $relationAttribute ) {
            return null;
        }
        
        return Relation::createRelation(
            $property->getName(),
            $relationAttribute->getRelationType(),
            $relationAttribute->getTargetEntity(),
            $registry->getClassMetaData($relationAttribute->getTargetEntity())->getEntity()->getPrimaryKey()->getDatabaseName(),
            $classMetaData->getEntity()->getPrimaryKey()->getDatabaseName(),
        );
    }
    
    
}