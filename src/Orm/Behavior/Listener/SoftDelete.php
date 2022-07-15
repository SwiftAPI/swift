<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Behavior\Listener;

use Cycle\ORM\Command\StoreCommand;
use Cycle\ORM\Heap\Node;
use Swift\Orm\Attributes\Behavior\Listen;
use Swift\Orm\Behavior\Event\Mapper\Command\OnDelete;

final class SoftDelete {
    
    public function __construct(
        private string $field = 'deletedAt',
    ) {
    }
    
    #[Listen( OnDelete::class )]
    public function __invoke( OnDelete $event ): void {
        $event->state->register( $this->field, $event->timestamp );
        
        // Replace Delete command to Store command
        if ( ! $event->command instanceof StoreCommand ) {
            $event->command = $event->mapper->queueUpdate( $event->entity, $event->node, $event->state );
        }
        
        // Node should be removed from heap
        $event->state->setStatus( Node::SCHEDULED_DELETE );
    }
    
}
