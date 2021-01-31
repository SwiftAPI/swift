<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation;

use JetBrains\PhpStorm\Pure;

/**
 * Trait LowercaseTrait
 * @package Swift\HttpFoundation
 */
trait LowercaseTrait {

    #[Pure] private static function lowercase( string $value ): string {
        return \strtr( $value, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz' );
    }

}