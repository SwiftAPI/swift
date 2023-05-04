<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Dbal\Arguments;


use Swift\Orm\Mapping\Definition\Entity;

class OrderBy implements ArgumentInterface {
    
    public function __construct(
        protected readonly string $orderBy,
        protected readonly ArgumentDirection $direction = ArgumentDirection::ASC,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public function apply( \Cycle\ORM\Select $query, Entity $entity ): \Cycle\ORM\Select {
        if ( $this->orderBy ) {
            if ( ! $entity->hasFieldByPropertyName( $this->orderBy ) ) {
                throw new \InvalidArgumentException(
                    sprintf( 'Field %s can not be used for ordering. The following options are available: %s',
                             $this->orderBy, implode( separator: ', ', array: array_map( static fn( Field $field ): string => $field->getName(), $entity->getFields() ) )
                    ) );
            }
            $query->orderBy( $this->orderBy, $this->direction->value );
        }
        
        return $query;
    }
    
    /**
     * @return string
     */
    public function getOrderBy(): string {
        return $this->orderBy;
    }
    
    /**
     * @return \Swift\Dbal\Arguments\ArgumentDirection
     */
    public function getDirection(): ArgumentDirection {
        return $this->direction;
    }
    
    
}