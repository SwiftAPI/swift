<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Mapping;

use Swift\Kernel\Attributes\DI;

/**
 * Class NamingStrategy
 * @package Swift\Model\Mapping
 */
#[DI( aliases: [NamingStrategyInterface::class . ' $entityMappingNamingStrategy'] )]
class NamingStrategy implements NamingStrategyInterface {

    /**
     * @inheritDoc
     */
    public function getIndexName( Table $table, array $fields, IndexType $indexType ): string {
        if ($indexType->getValue() === IndexType::PRIMARY) {
            return 'PRIMARY';
        }

        $names = array_map( static fn (Field $field) => $field->getDatabaseName(), $fields);
        $full = [
            $table->getDatabaseName(),
            $indexType->getValue(),
            ...$names,
        ];

        return strtolower(implode('_', $full));
    }
}