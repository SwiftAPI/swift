<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Utilities;

use Closure;
use Swift\DependencyInjection\Attributes\DI;

#[DI( autowire: false )]
class Callback {

    public static function createCallbackForPayload( mixed $payload ): Closure {
        $reference = $payload;

        return static function () use ( $reference ) {
            return $reference;
        };
    }

}