<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Dbal;


use JetBrains\PhpStorm\Deprecated;

/**
 * Class QueryType
 * @package Swift\Orm\Query
 */
#[Deprecated]
enum QueryType: string {
    
    case SELECT = 'SELECT';
    case UPDATE = 'UPDATE';
    case DELETE = 'DELETE';
    case CREATE = 'CREATE';
    case ALTER = 'ALTER';
    
}