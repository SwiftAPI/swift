<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Dbal\Arguments;


use Swift\GraphQl\Attributes\Field;
use Swift\Orm\Mapping\Definition\Entity;
use Swift\Dbal\QueryBuilder;

class GroupBy implements ArgumentInterface {
    
    public function __construct(
        protected string $groupBy,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public function apply( \Cycle\ORM\Select $query, Entity $entity ): \Cycle\ORM\Select {
        if ( $this->groupBy ) {
            if ( ! $entity->hasFieldByPropertyName( $this->groupBy ) ) {
                throw new \InvalidArgumentException(
                    sprintf( 'Field %s can not be used for grouping. The following options are available: %s',
                             $this->groupBy, implode( separator: ', ', array: array_keys( array_map( static fn( Field $field ): string => $field->getName(), $entity->getFields() ) ) )
                    ) );
            }
            $query->groupBy( $this->groupBy );
        }
        
        return $query;
    }
    
    /**
     * @return string
     */
    public function getGroupBy(): string {
        return $this->groupBy;
    }
    
    /**
     * @param string $groupBy
     */
    public function setGroupBy( string $groupBy ): void {
        $this->groupBy = $groupBy;
    }
    
    
    
}