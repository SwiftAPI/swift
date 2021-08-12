<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Arguments;

use Swift\Model\Mapping\Table;
use Swift\Model\Query\QueryBuilder;

/**
 * Interface ArgumentInterface
 * @package Swift\Model\Arguments
 */
interface ArgumentInterface {

    /**
     * Apply argument to query
     *
     * @param \Swift\Model\Query\QueryBuilder $query
     * @param \Swift\Model\Mapping\Table      $table
     *
     * @return QueryBuilder
     */
    public function apply( QueryBuilder $query, Table $table ): QueryBuilder;

}