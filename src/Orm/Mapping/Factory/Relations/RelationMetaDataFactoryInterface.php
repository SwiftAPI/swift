<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping\Factory\Relations;

use Swift\DependencyInjection\Attributes\DI;
use Swift\Orm\Mapping\ClassMetaData;
use Swift\Orm\Mapping\Definition\Relation\EntitiesConnection;
use Swift\Orm\Mapping\RegistryInterface;

#[DI( tags: [ 'orm.relation_metadata_factory' ] )]
interface RelationMetaDataFactoryInterface {
    
    /**
     * Determine whether class support a given class
     *
     * @param \ReflectionProperty                                     $property
     * @param \Swift\Orm\Mapping\ClassMetaData                        $classMetadata
     * @param \Swift\Orm\Mapping\RegistryInterface                    $registry
     *
     * @return bool
     */
    public function supports( \ReflectionProperty $property, ClassMetadata $classMetadata, RegistryInterface $registry ): bool;
    
    public function createRelationMetaData( \ReflectionProperty $property, ClassMetaData $classMetaData, RegistryInterface $registry): void;
    
}