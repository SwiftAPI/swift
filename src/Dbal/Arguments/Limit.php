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

class Limit implements ArgumentInterface {
    
    public function __construct(
        protected int $limit,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public function apply( \Cycle\ORM\Select $query, Entity $entity ): \Cycle\ORM\Select {
        if ( $this->limit > 0 ) {
            $query->limit( $this->limit );
        }
        
        return $query;
    }
    
    /**
     * @return int
     */
    public function getLimit(): int {
        return $this->limit;
    }
    
    /**
     * @param int $limit
     */
    public function setLimit( int $limit ): void {
        $this->limit = $limit;
    }
    
    
    
}