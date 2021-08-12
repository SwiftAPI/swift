<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Query;


use Swift\Kernel\TypeSystem\Enum;

/**
 * Class QueryType
 * @package Swift\Model\Query
 */
class QueryType extends Enum {

    public const SELECT = 'SELECT';
    public const UPDATE = 'UPDATE';
    public const DELETE = 'DELETE';
    public const CREATE = 'CREATE';
    public const ALTER  = 'ALTER';

}