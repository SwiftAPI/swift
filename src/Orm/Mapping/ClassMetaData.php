<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping;


use Swift\Code\ReflectionClass;
use Swift\DependencyInjection\Attributes\DI;

/**
 * Class ClassMetaData
 * @package Swift\Orm\Mapping
 */
#[DI( autowire: false )]
class ClassMetaData {
    
    /**
     * ClassMetaData constructor.
     */
    public function __construct(
        protected \Swift\Orm\Mapping\Definition\Entity $entity,
        protected ?ReflectionClass                     $reflectionClass = null,
    ) {
        if ( ! $reflectionClass ) {
            $this->reflectionClass = new ReflectionClass( $entity->getClassName() );
        }
    }
    
    /**
     * @return \Swift\Orm\Mapping\Definition\Entity
     */
    public function getEntity(): \Swift\Orm\Mapping\Definition\Entity {
        return $this->entity;
    }
    
    /**
     * @return \Swift\Code\ReflectionClass|null
     */
    public function getReflectionClass(): ?ReflectionClass {
        if ( ! isset( $this->reflectionClass ) || ! $this->reflectionClass ) {
            $this->reflectionClass = new ReflectionClass( $this->entity->getClassName() );
        }
        
        return $this->reflectionClass;
    }
    
    public function __serialize(): array {
        return [ 'entity' => $this->entity ];
    }
    
}