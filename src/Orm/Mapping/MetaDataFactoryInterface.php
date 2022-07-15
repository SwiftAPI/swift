<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping;

use Swift\DependencyInjection\Attributes\DI;

#[DI( tags: ['orm.metadata_factory'] )]
interface MetaDataFactoryInterface {
    
    /**
     * Determine whether class support a given class
     *
     * @param \Swift\Orm\Mapping\ClassMetaData         $classMetaData
     * @param \Swift\Orm\Mapping\RegistryInterface     $registry
     *
     * @return bool
     */
    public function supports( ClassMetadata $classMetaData, RegistryInterface $registry ): bool;
    
    /**
     * Add or modify data in class
     *
     * @param \Swift\Orm\Mapping\ClassMetadata         $classMetaData
     * @param \Swift\Orm\Mapping\RegistryInterface     $registry
     *
     * @return \Swift\Orm\Mapping\ClassMetaData
     */
    public function create( ClassMetadata $classMetaData, RegistryInterface $registry ): ClassMetaData;
    
    
}