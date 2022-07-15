<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Behavior\Dispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

final class Dispatcher implements EventDispatcherInterface {
    
    private ListenerProviderInterface $listenerProvider;
    
    public function __construct( ListenerProviderInterface $listenerProvider ) {
        $this->listenerProvider = $listenerProvider;
    }
    
    public function dispatch( object $event ): object {
        /** @var callable $listener */
        foreach ( $this->listenerProvider->getListenersForEvent( $event ) as $listener ) {
            if ( $event instanceof StoppableEventInterface && $event->isPropagationStopped() ) {
                return $event;
            }
            
            $e = $event;
            $listener( $e );
        }
        
        return $event;
    }
}
