<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Mapping\Definition\Relation;


use Swift\Orm\Attributes\Relation\BelongsTo;
use Swift\Orm\Attributes\Relation\HasMany;
use Swift\Orm\Attributes\Relation\HasOne;
use Swift\Orm\Attributes\Relation\ManyToMany;
use Swift\Orm\Attributes\Relation\RelationFieldInterface;

enum EntityRelationType: string {
    
    case HAS_ONE = 'HAS_ONE';
    case BELONGS_TO = 'BELONGS_TO';
    case REFERS_TO = 'REFERS_TO';
    case HAS_MANY = 'HAS_MANY';
    case MANY_TO_MANY = 'MANY_TO_MANY';
    case EMBEDDED = 'EMBEDDED';
    
    public function toRelationAttribute( string $targetEntity ): RelationFieldInterface {
        return match($this) {
            self::HAS_ONE => new HasOne( $targetEntity ),
            self::BELONGS_TO => new BelongsTo( $targetEntity ),
            self::HAS_MANY => new HasMany( $targetEntity ),
            self::MANY_TO_MANY => new ManyToMany( $targetEntity ),
        };
    }
    
}