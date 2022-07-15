<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\DependencyInjection;


enum OrmDiTags: string {
    
    case ORM_ANNOTATED = 'orm.annotated';
    case ORM_TABLE = 'orm.table';
    case ORM_ENTITY = 'orm.entity';
    case ORM_EMBEDDABLE = 'orm.embeddable';
    case ORM_HOOK = 'orm.hook';
    case ORM_EVENTLISTENER = 'orm.eventlistener';
    case ORM_OPTIMISTIC_LOCK = 'orm.optimistic_lock';
    case ORM_SCHEMA_MODIFIER = 'orm.schema.modifier';
    case ORM_LIFECYCLE = 'orm.lifecycle';
    
}