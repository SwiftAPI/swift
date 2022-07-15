<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping;

use Swift\Orm\Mapping\Definition\Entity;
use Swift\Orm\Mapping\Definition\Field;
use Swift\Orm\Mapping\Definition\IndexType;

/**
 * Interface NamingStrategyInterface
 * @package Swift\Orm\Mapping
 */
interface NamingStrategyInterface {

    /**
     * Get index name for given field(s) in given entity for the given index type
     *
     * @param Entity $entity
     * @param Field[] $fields
     * @param IndexType $indexType
     *
     * @return string
     */
    public function getIndexName( Entity $entity, array $fields, IndexType $indexType ): string;
    
    /**
     * Get entity name for connection entity between multiple entities
     *
     * @param Entity[] $entities
     * @param array $fields
     *
     * @return string
     */
    public function getEntitiesConnectionEntityName( array $entities, array $fields ): string;
    
    /**
     * Get field name for field reference in connection entity
     *
     * @param \Swift\Orm\Mapping\Definition\Entity $entity
     * @param string                     $fieldName
     *
     * @return mixed
     */
    public function getEntitiesConnectionFieldName( Entity $entity, string $fieldName ): string;

}