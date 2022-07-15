<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Behavior\Listener\Uuid;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Swift\Orm\Attributes\Behavior\Listen;
use Swift\Orm\Behavior\Event\Mapper\Command\OnCreate;

/**
 * Uses a version 5 (name-based) UUID based on the SHA-1 hash of a namespace ID and a name
 */
final class Uuid5 {
    public function __construct(
        private readonly string|UuidInterface $namespace,
        private readonly string               $name,
        private readonly string               $field = 'uuid'
    ) {
    }
    
    #[Listen( OnCreate::class )]
    public function __invoke( OnCreate $event ): void {
        if ( ! isset( $event->state->getData()[ $this->field ] ) ) {
            $event->state->register( $this->field, Uuid::uuid5( $this->namespace, $this->name ) );
        }
    }
}
