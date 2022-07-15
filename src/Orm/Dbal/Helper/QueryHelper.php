<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Dbal\Helper;


use JetBrains\PhpStorm\Deprecated;
use Swift\Orm\Mapping\ClassMetaData;

#[Deprecated]
class QueryHelper {
    
    /**
     * @param \Swift\Orm\Mapping\ClassMetaData $classMetaData
     *
     * @return array
     */
    public function getFieldsForSelection( ClassMetaData $classMetaData ): array {
        $fieldNames = [];
        
        foreach ($classMetaData->getEntity()->getFields() as $field) {
            $fieldNames[] = sprintf('%s.%s', $classMetaData->getEntity()->getDatabaseName(), $field->getDatabaseName());
        }
        
        return $fieldNames;
    }
    
}