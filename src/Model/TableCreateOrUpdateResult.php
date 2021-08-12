<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model;

use Swift\Kernel\Attributes\DI;
use Swift\Model\Query\QueryType;

#[DI( autowire: false )]
class TableCreateOrUpdateResult {

    public function __construct(
        private QueryType $queryType,
        private array $nonExistingColumns,
        private array $nonExistingIndexes,
    ) {
    }

    /**
     * @return \Swift\Model\Query\QueryType
     */
    public function getQueryType(): QueryType {
        return $this->queryType;
    }

    /**
     * @return array
     */
    public function getNonExistingColumns(): array {
        return $this->nonExistingColumns;
    }

    /**
     * @return array
     */
    public function getNonExistingIndexes(): array {
        return $this->nonExistingIndexes;
    }



}