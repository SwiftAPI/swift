<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Behavior;

use Swift\DependencyInjection\Attributes\DI;

#[DI( tags: [ 'orm.schema.modifier', 'orm.annotated' ] )]
interface SchemaModifierInterface extends \Cycle\Schema\SchemaModifierInterface {
    
}