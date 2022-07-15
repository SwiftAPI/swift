<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Dbal\Enums;


use JetBrains\PhpStorm\Deprecated;

/**
 * MySql Query Action Type which can be performed on columns and indexes
 */
#[Deprecated]
enum QueryAction: string {

    case ADD = 'ADD';
    case MODIFY = 'MODIFY';
    case DROP = 'DROP';

}