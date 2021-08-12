<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Mapping;

/**
 * Interface NamingStrategyInterface
 * @package Swift\Model\Mapping
 */
interface NamingStrategyInterface {

    /**
     * Get index name for given field(s) in given table table for the given index type
     *
     * @param Table $table
     * @param Field[] $fields
     * @param IndexType $indexType
     *
     * @return string
     */
    public function getIndexName( Table $table, array $fields, IndexType $indexType ): string;

}