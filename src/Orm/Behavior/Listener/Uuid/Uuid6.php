<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Behavior\Listener\Uuid;

use Swift\Orm\Attributes\Behavior\Listen;
use Swift\Orm\Behavior\Event\Mapper\Command\OnCreate;
use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Uuid;

/**
 * Ordered-Time Uses a version 6 (ordered-time) UUID from a host ID, sequence number, and the current time
 */
final class Uuid6 {
    
    public function __construct(
        private readonly string         $field = 'uuid',
        private Hexadecimal|string|null $node = null,
        private readonly ?int           $clockSeq = null
    ) {
    }
    
    #[Listen( OnCreate::class )]
    public function __invoke( OnCreate $event ): void {
        if ( \is_string( $this->node ) ) {
            $this->node = new Hexadecimal( $this->node );
        }
        
        if ( ! isset( $event->state->getData()[ $this->field ] ) ) {
            $event->state->register( $this->field, Uuid::uuid6( $this->node, $this->clockSeq ) );
        }
    }
}
