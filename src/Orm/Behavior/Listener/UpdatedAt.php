<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Behavior\Listener;

use Cycle\ORM\Command\StoreCommandInterface;
use Swift\Orm\Attributes\Behavior\Listen;
use Swift\Orm\Behavior\Event\Mapper\Command\OnCreate;
use Swift\Orm\Behavior\Event\Mapper\Command\OnUpdate;

final class UpdatedAt {
    
    public function __construct(
        private readonly string $field = 'updatedAt',
        private readonly bool $nullable = false
    ) {
    }
    
    #[Listen( OnUpdate::class )]
    public function __invoke( OnUpdate $event ): void {
        if ( $event->command instanceof StoreCommandInterface ) {
            $event->command->registerAppendix( $this->field, $event->timestamp );
        }
    }
    
    #[Listen( OnCreate::class )]
    public function onCreate( OnCreate $event ): void {
        if ( ! $this->nullable ) {
            $event->state->register( $this->field, $event->timestamp );
        }
    }
}
