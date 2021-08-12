<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Query;

use Swift\Kernel\Attributes\Autowire;
use Swift\Model\Arguments\Arguments;
use Swift\Model\EntityInterface;
use Swift\Model\EntityManager;
use Swift\Model\Utilities\Callback;

#[Autowire]
class ResultFactory {

    public function createResultSet( array $results, EntityInterface $entity, ?QueryBuilder $queryBuilder, array $state, ?Arguments $arguments, EntityManager $entityManager ): ResultSet {
        $resultSet = new ResultSet(
            Callback::createCallbackForPayload( $entity ),
            Callback::createCallbackForPayload( $queryBuilder ),
            Callback::createCallbackForPayload( $state ),
            Callback::createCallbackForPayload( $arguments ),
        );
        foreach ( $results as $result ) {
            $resultSet[] = $this->createResult( $result->toArray(), $entity, $entityManager );
        }

        return $resultSet;
    }

    public function createResult( array $result, EntityInterface $entity, EntityManager $entityManager ): Result {
        $item = new Result(
            Callback::createCallbackForPayload( $entity ),
            Callback::createCallbackForPayload( $entityManager->getClassMetaDataFactory()->getClassMetaData( $entity::class ) ),
        );
        foreach ( $result as $key => $value ) {
            $field    = $entityManager->getClassMetaDataFactory()->getClassMetaData( $entity::class )->getTable()->getFieldByDatabaseName( $key );

            if (!$field) {
                continue;
            }

            $item->{$field->getPropertyName()} = $entityManager->getTypeTransformer()->transformToPhpValue( $field->getType()->getName(), $value, $entity::class, $field );
        }
        // @TODO: Fix joins
//            foreach ( $this->getClassMeta( $entity::class )->getJoins() as $name => $join ) {
//                $item->{$name} = $join->instance->findMany(
//                    [],
//                    new Arguments( null, null, null, null, null, array(
//                        new Where( $join->joiningEntityField, Where::EQUALS, $item->{$join->currentEntityField} )
//                    ) ),
//                );
//            }

        return $item;
    }

}