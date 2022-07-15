<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Schema\Generator;


class MergeIndexes implements \Cycle\Schema\GeneratorInterface {
    
    protected readonly Configurator $configurator;
    
    public function __construct(
        protected readonly \Swift\Orm\Mapping\ClassMetaDataFactory    $classMetaDataFactory,
        protected readonly \Swift\Orm\Mapping\NamingStrategyInterface $namingStrategy,
        protected readonly \Swift\Orm\Mapping\Driver\AttributeReader  $reader,
        protected readonly int                                        $tableNamingStrategy = \Cycle\Annotated\Entities::TABLE_NAMING_PLURAL,
    ) {
        $this->configurator = new Configurator( $this->classMetaDataFactory, $this->namingStrategy, $this->reader, $this->tableNamingStrategy );
    }
    
    /**
     * @inheritDoc
     */
    public function run( \Cycle\Schema\Registry $registry ): \Cycle\Schema\Registry {
        foreach ( $registry as $e ) {
            if ( ! $e->getClass() ) {
                continue;
            }
            
            $metaData = $this->classMetaDataFactory->getClassMetaData( $e->getClass() );
            
            if (! $metaData ) {
                continue;
            }
            
            $entityDefinition = $metaData->getEntity();
            
            $this->configurator->initIndexes( $registry, $entityDefinition, $e );
        }
        
        return $registry;
    }
    
    
    
    
}