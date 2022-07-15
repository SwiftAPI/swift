<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Utilities;

use Swift\Dbal\Exceptions\NoResultException;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Orm\Dbal\EntityResultInterface;
use Swift\Orm\Dbal\QueryFactory;
use Swift\Orm\Entity\Arguments;
use Swift\Orm\Entity\EntityInterface;
use Swift\Orm\Mapping\ClassMetaDataFactory;
use Swift\Orm\Types\TypeTransformer;

#[Autowire]
class Hydrator {
    
    public function __construct(
        private readonly QueryFactory         $queryFactory,
        private readonly TypeTransformer      $typeTransformer,
        private readonly ClassMetaDataFactory $classMetaDataFactory,
    ) {
    }
    
    /**
     * Attach additional data to provided entity instance
     *
     * @param \Swift\Orm\Entity\EntityInterface $entity
     * @param string                     $pkName
     * @param int                        $pk
     *
     * @return \Swift\Orm\Dbal\EntityResultInterface
     */
    public function hydrate( EntityInterface $entity, string $pkName, int $pk ): EntityResultInterface {
        $selectQuery = $this->queryFactory->getSelectQuery( $entity::class, [ $pkName => $pk ], new Arguments( 0, 1 ) );
        $result = $selectQuery?->fetch()?->toArray();
        
        if (empty($result)) {
            throw new NoResultException( 'Could not hydrate Entity by pk, this is likely a bug' );
        }
        
        $entity?->setClassMetaDataReference( Callback::createCallbackForPayload( $this->classMetaDataFactory->getClassMetaData( $entity::class ) ) );
        $entity?->setup();
        
        foreach ($result as $key => $value) {
            $field = $this->classMetaDataFactory->getClassMetaData( $entity::class )->getEntity()->getFieldByDatabaseName( $key );
    
            if ( ! $field ) {
                continue;
            }
            
            $entity->{$field->getPropertyName()} = $this->typeTransformer->transformToPhpValue( $field->getType()->getName(), $value, $entity::class, $field );
        }
        
        return $entity;
    }
    
}