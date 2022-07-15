<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping\Definition;

/**
 * Class IndexTypes
 * @package Swift\Orm\Mapping
 */
enum IndexType {

    case PRIMARY;
    case INDEX;
    case UNIQUE;
    
    public static function getIndexTypeForFieldAttribute( \Swift\Orm\Attributes\Field $fieldAttribute ): ?self {
        if ( $fieldAttribute->isPrimaryKey() ) {
            return self::PRIMARY;
        }
        
        if ( $fieldAttribute->isUnique() ) {
            return self::UNIQUE;
        }
        
        if ( $fieldAttribute->isIndex() ) {
            return self::INDEX;
        }
        
        return null;
    }

}