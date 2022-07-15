<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Relation;


use Cycle\ORM\Reference\ReferenceInterface;
use Cycle\ORM\Relation\RelationInterface;
use Cycle\ORM\Transaction\Pool;
use Cycle\ORM\Transaction\Tuple;

class HasMany extends \Cycle\ORM\Relation\HasMany {
    
    public function prepare( Pool $pool, Tuple $tuple, mixed $related, bool $load = true ): void {
        $node     = $tuple->node;
        $original = $node->getRelation( $this->getName() );
        $tuple->state->setRelation( $this->getName(), $related );
        
        if ( $original instanceof ReferenceInterface ) {
            if ( ! $load && $this->compareReferences( $original, $related ) && ! $original->hasValue() ) {
                $tuple->state->setRelationStatus( $this->getName(), RelationInterface::STATUS_RESOLVED );
                
                return;
            }
            $original = $this->resolve( $original, true );
            $node->setRelation( $this->getName(), $original );
        }
        
        if ( $related instanceof ReferenceInterface ) {
            $related = $this->resolve( $related, true );
            $tuple->state->setRelation( $this->getName(), $related );
        }
    
        $related ??= [];
        foreach ( $this->calcDeleted( $related, $original ?? [] ) as $item ) {
            $this->deleteChild( $pool, $tuple, $item );
        }
        
        if ( \count( $related ) === 0 ) {
            $tuple->state->setRelationStatus( $this->getName(), RelationInterface::STATUS_RESOLVED );
            
            return;
        }
        $tuple->state->setRelationStatus( $this->getName(), RelationInterface::STATUS_PROCESS );
        
        // $relationName = $this->getTargetRelationName()
        // Store new and existing items
        foreach ( $related as $item ) {
            $rTuple = $pool->attachStore( $item, true );
            $this->assertValid( $rTuple->node );
            if ( $this->isNullable() ) {
                // todo?
                // $rNode->setRelationStatus($relationName, RelationInterface::STATUS_DEFERRED);
            }
        }
    }
    
}