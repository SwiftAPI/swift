<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Behavior\Listener\Uuid;

use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Uuid;
use Swift\Orm\Attributes\Behavior\Listen;
use Swift\Orm\Behavior\Event\Mapper\Command\OnCreate;

/**
 * A version 1 UUID uses the current time, along with the MAC address (or node) for a network interface on the local machine.
 */
final class Uuid1 {
    
    public function __construct(
        private readonly string                      $field = 'uuid',
        private readonly Hexadecimal|int|string|null $node = null,
        private readonly ?int $clockSeq = null
    ) {
    }
    
    #[Listen( OnCreate::class )]
    public function __invoke( OnCreate $event ): void {
        if ( ! isset( $event->state->getData()[ $this->field ] ) ) {
            $event->state->register( $this->field, Uuid::uuid1( $this->node, $this->clockSeq ) );
        }
    }
}
