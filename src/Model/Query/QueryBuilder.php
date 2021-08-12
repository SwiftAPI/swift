<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Query;


use Dibi\Fluent;
use Swift\Kernel\Attributes\DI;

/**
 * Class QueryBuilder
 * @package Swift\Model\Query
 *
 * Extends Dibi QueryBuilder class for enhanced features and ease of use in Entity Management
 *          
 *
 * SQL builder via queryBuilder interfaces.
 *
 * @method QueryBuilder select(...$field)
 * @method QueryBuilder distinct()
 * @method QueryBuilder from($table, ...$args = null)
 * @method QueryBuilder where(...$cond)
 * @method QueryBuilder groupBy(...$field)
 * @method QueryBuilder having(...$cond)
 * @method QueryBuilder orderBy(...$field)
 * @method QueryBuilder limit(int $limit)
 * @method QueryBuilder offset(int $offset)
 * @method QueryBuilder join(...$table)
 * @method QueryBuilder leftJoin(...$table)
 * @method QueryBuilder innerJoin(...$table)
 * @method QueryBuilder rightJoin(...$table)
 * @method QueryBuilder outerJoin(...$table)
 * @method QueryBuilder as(...$field)
 * @method QueryBuilder on(...$cond)
 * @method QueryBuilder and(...$cond)
 * @method QueryBuilder or(...$cond)
 * @method QueryBuilder using(...$cond)
 * @method QueryBuilder update(...$cond)
 * @method QueryBuilder insert(...$cond)
 * @method QueryBuilder delete(...$cond)
 * @method QueryBuilder into(...$cond)
 * @method QueryBuilder values(...$cond)
 * @method QueryBuilder set(...$args)
 * @method QueryBuilder asc()
 * @method QueryBuilder desc()
 */
#[DI( autowire: false )]
class QueryBuilder extends Fluent {



}