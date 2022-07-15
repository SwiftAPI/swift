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
use Swift\Orm\Attributes\Behavior\Listen;
use Swift\Orm\Behavior\Event\Mapper\Command\OnCreate;

/**
 * They are randomly-generated and do not contain any information about the time they are created or the machine that generated them.
 */
final class Uuid4 {
    
    public function __construct(
        private readonly string $field = 'uuid'
    ) {
    }
    
    #[Listen( OnCreate::class )]
    public function __invoke( OnCreate $event ): void {
        if ( ! isset( $event->state->getData()[ $this->field ] ) ) {
            $event->state->register( $this->field, Uuid::uuid4() );
        }
    }
}
