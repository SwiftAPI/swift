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
use Swift\Orm\Behavior\Event\Mapper\Command\OnCreate;

final class CreatedAt {
    
    public function __construct(
        private readonly string $field = 'createdAt'
    ) {
    }
    
    #[Listen( OnCreate::class )]
    public function __invoke( OnCreate $event ): void {
        $event->state->register( $this->field, $event->timestamp );
    }
    
}
