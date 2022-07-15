<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping;

use Swift\DependencyInjection\Attributes\DI;
use Swift\Orm\Mapping\Definition\Entity;
use Swift\Orm\Mapping\Definition\Field;
use Swift\Orm\Mapping\Definition\IndexType;

/**
 * Class NamingStrategy
 * @package Swift\Orm\Mapping
 */
#[DI( aliases: [NamingStrategyInterface::class . ' $entityMappingNamingStrategy'] )]
class NamingStrategy implements NamingStrategyInterface {

    /**
     * @inheritDoc
     */
    public function getIndexName( Entity $entity, array $fields, IndexType $indexType ): string {
        if ($indexType === IndexType::PRIMARY) {
            return 'PRIMARY';
        }

        $names = array_map( static fn (Field $field) => $field->getDatabaseName(), $fields);
        $full = [
            $entity->getDatabaseName(),
            $indexType->name,
            ...$names,
        ];

        return strtolower(implode('_', $full));
    }
    
    /**
     * @inheritDoc
     */
    public function getEntitiesConnectionEntityName( array $entities, array $fields ): string {
        $names = array_map( static fn (Entity $entity) => $entity->getDatabaseName(), $entities);
        $full  = [
            ...$names,
            'connection',
        ];
        
        return strtolower(implode('_', $full));
    }
    
    /**
     * @inheritDoc
     */
    public function getEntitiesConnectionFieldName( Entity $entity, string $fieldName ): string {
        return strtolower( $entity->getDatabaseName() . '_' . $fieldName );
    }
}