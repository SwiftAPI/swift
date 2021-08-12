<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Query;


/**
 * Class Query
 * @package Swift\Model\Query
 */
abstract class Query {

    /**
     * Query constructor.
     */
    public function __construct(
        protected QueryType $queryType,
    ) {
    }

    /**
     * @return QueryType
     */
    public function getQueryType(): QueryType {
        return $this->queryType;
    }

    /**
     * Get query parsed to sql
     *
     * @return string
     */
    abstract public function getSql(): string;

}