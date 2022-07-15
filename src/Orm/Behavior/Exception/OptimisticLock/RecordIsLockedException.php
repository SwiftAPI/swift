<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Behavior\Exception\OptimisticLock;

use Cycle\ORM\Heap\Node;

class RecordIsLockedException extends OptimisticLockException {
    
    public function __construct( Node $node ) {
        $message = sprintf( 'The `%s` record is locked.', $node->getRole() );
        
        parent::__construct( $message );
    }
    
}
