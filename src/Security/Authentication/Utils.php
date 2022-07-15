<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication;

use Swift\DependencyInjection\Attributes\DI;

/**
 * Class Utils
 * @package Swift\Security\Authentication
 */
#[DI(autowire: false)]
class Utils {

    public static float $TOKEN_VALIDITY = 5;

    public static function getNewTokenExpiry(): \DateTime {
        return (new \DateTime())->modify(sprintf("+ %s hours", self::$TOKEN_VALIDITY));
    }

}