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
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Uses a version 3 (name-based) UUID based on the MD5 hash of a namespace ID and a name
 */
final class Uuid3 {
    
    public function __construct(
        private readonly string|UuidInterface $namespace,
        private readonly string               $name,
        private readonly string               $field = 'uuid'
    ) {
    }
    
    #[Listen( OnCreate::class )]
    public function __invoke( OnCreate $event ): void {
        if ( ! isset( $event->state->getData()[ $this->field ] ) ) {
            $event->state->register( $this->field, Uuid::uuid3( $this->namespace, $this->name ) );
        }
    }
}
