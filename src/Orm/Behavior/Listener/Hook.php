<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Behavior\Listener;


use Swift\Orm\Attributes\Behavior\Listen;
use Swift\Orm\Behavior\Event\Mapper\QueueCommand;

final class Hook {
    
    /** @var callable */
    private $callable;
    
    public function __construct(
        callable      $callable,
        private array $events
    ) {
        $this->callable = $callable;
    }
    
    #[Listen( QueueCommand::class )]
    public function __invoke( QueueCommand $event ): void {
        if ( ! \in_array( $event::class, $this->events, true ) ) {
            return;
        }
        
        \call_user_func( $this->callable, $event );
    }
}
