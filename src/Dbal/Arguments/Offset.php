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

class Offset implements ArgumentInterface {
    
    public function __construct(
        protected int $offset,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public function apply( \Cycle\ORM\Select $query, Entity $entity ): \Cycle\ORM\Select {
        if ( $this->offset > 0 ) {
            $query->offset( $this->offset );
        }
        
        return $query;
    }
    
    /**
     * @return int
     */
    public function getOffset(): int {
        return $this->offset;
    }
    
    /**
     * @param int $offset
     */
    public function setOffset( int $offset ): void {
        $this->offset = $offset;
    }
    
    
    
}