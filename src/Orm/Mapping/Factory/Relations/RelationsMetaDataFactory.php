<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping\Factory\Relations;

use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Orm\Mapping\ClassMetaData;
use Swift\Orm\Mapping\MetaDataFactoryInterface;
use Swift\Orm\Mapping\RegistryInterface;

#[Autowire]
class RelationsMetaDataFactory implements MetaDataFactoryInterface {
    
    /** @var \Swift\Orm\Mapping\Factory\Relations\RelationMetaDataFactoryInterface[] $relationMetaDataFactories */
    protected array $relationMetaDataFactories = [];
    
    public function __construct(
        protected RegistryInterface $registry,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public function supports( ClassMetadata $classMetaData, \Swift\Orm\Mapping\RegistryInterface $registry ): bool {
        return !empty($this->relationMetaDataFactories);
    }
    
    
    public function create( ClassMetaData $classMetaData, \Swift\Orm\Mapping\RegistryInterface $registry ): ClassMetaData {
        foreach ( $this->relationMetaDataFactories as $metaDataFactory ) {
            foreach ( $classMetaData->getReflectionClass()->getProperties() as $property ) {
                if (!$metaDataFactory->supports( $property, $classMetaData, $registry )) {
                    continue;
                }
    
                $metaDataFactory->createRelationMetaData( $property, $classMetaData, $registry );
            }
            
        }
        
        return $classMetaData;
    }
    
    #[Autowire]
    public function setRelationMetaDataFactories( #[Autowire( tag: 'orm.relation_metadata_factory' )] ?iterable $relationMetaDataFactories ): void {
        if ( ! $relationMetaDataFactories ) {
            return;
        }
        
        $relationMetaDataFactories = iterator_to_array( $relationMetaDataFactories );
        
        foreach ( $relationMetaDataFactories as $relationMetaDataFactory ) {
            $this->relationMetaDataFactories[ $relationMetaDataFactory::class ] = $relationMetaDataFactory;
        }
    }
    
}