<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Attributes\Behavior;

use Attribute;
use Swift\Orm\Behavior\Event\MapperEvent;

#[Attribute( flags: Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE )]
#[\AllowDynamicProperties]
final class Listen {
    
    /**
     * @param class-string<MapperEvent> $event
     */
    public function __construct(
        public string $event,
    ) {
    }
}
