<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Schema\Generator;


use Cycle\Annotated\Annotation\Embeddable;
use Cycle\Annotated\Exception\AnnotationException;
use Cycle\ORM\Mapper\Mapper;
use Cycle\ORM\Mapper\StdMapper;
use Cycle\ORM\Relation;
use Cycle\ORM\Schema;
use Cycle\Schema\Definition\Map\OptionMap;
use Cycle\Schema\Registry;
use Cycle\Schema\Relation\RelationSchema;
use Swift\Orm\Types\Typecast;

class Entities implements \Cycle\Schema\GeneratorInterface {
    
    public const OPTION_MAP = [
        'nullable'        => Relation::NULLABLE,
        'cascade'         => Relation::CASCADE,
        'load'            => Relation::LOAD,
        'innerKey'        => Relation::INNER_KEY,
        'outerKey'        => Relation::OUTER_KEY,
        'morphKey'        => Relation::MORPH_KEY,
        'through'         => Relation::THROUGH_ENTITY,
        'throughInnerKey' => Relation::THROUGH_INNER_KEY,
        'throughOuterKey' => Relation::THROUGH_OUTER_KEY,
        'throughWhere'    => Relation::THROUGH_WHERE,
        'where'           => Relation::WHERE,
        'collection'      => Relation::COLLECTION_TYPE,
        'orderBy'         => Relation::ORDER_BY,
        'fkCreate'        => RelationSchema::FK_CREATE,
        'fkAction'        => RelationSchema::FK_ACTION,
        'fkOnDelete'      => RelationSchema::FK_ON_DELETE,
        'indexCreate'     => RelationSchema::INDEX_CREATE,
        'morphKeyLength'  => RelationSchema::MORPH_KEY_LENGTH,
        'embeddedPrefix'  => RelationSchema::EMBEDDED_PREFIX,
        
        // deprecated
        'though'          => Relation::THROUGH_ENTITY,
        'thoughInnerKey'  => Relation::THROUGH_INNER_KEY,
        'thoughOuterKey'  => Relation::THROUGH_OUTER_KEY,
        'thoughWhere'     => Relation::THROUGH_WHERE,
    ];
    
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
    public function run( Registry $registry ): Registry {
        foreach ( $this->classMetaDataFactory->getAllClassMetaData() as $metaData ) {
            $entityDefinition = $metaData->getEntity();
            
            $definition = $this->configurator->initEntity( $entityDefinition );
            
            $this->configurator->initFields( $entityDefinition, $definition );
            
            $this->configurator->initRelations( $definition, $metaData->getReflectionClass(), $registry );
            
            $this->configurator->initModifiers( $definition, $metaData->getReflectionClass() );
            
            $registry->register( $definition )->linkTable( $definition, 'default', $entityDefinition->getDatabaseName() );
        }
        
        return $registry;
    }
    

    
}