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
use Ramsey\Uuid\Type\Integer as IntegerObject;
use Ramsey\Uuid\Uuid;

/**
 * UUID v2 uses the current time, along with the MAC address (or node) for a network interface on the local machine.
 * Additionally, a version 2 UUID replaces the low part of the time field with a local identifier such as the user ID or
 * group ID of the local account that created the UUID.
 */
final class Uuid2 {
    
    public function __construct(
        private readonly int              $localDomain,
        private readonly string           $field = 'uuid',
        private IntegerObject|string|null $localIdentifier = null,
        private Hexadecimal|string|null   $node = null,
        private readonly ?int             $clockSeq = null
    ) {
    }
    
    #[Listen( OnCreate::class )]
    public function __invoke( OnCreate $event ): void {
        if ( \is_string( $this->localIdentifier ) ) {
            $this->localIdentifier = new IntegerObject( $this->localIdentifier );
        }
        if ( \is_string( $this->node ) ) {
            $this->node = new Hexadecimal( $this->node );
        }
        
        if ( ! isset( $event->state->getData()[ $this->field ] ) ) {
            $event->state->register(
                $this->field,
                Uuid::uuid2( $this->localDomain, $this->localIdentifier, $this->node, $this->clockSeq )
            );
        }
    }
    
}
