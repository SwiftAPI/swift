<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Behavior;

use Swift\DependencyInjection\Attributes\DI;
use Swift\Orm\Behavior\Event\Mapper\Command\OnCreate;
use Swift\Orm\Behavior\Event\Mapper\Command\OnDelete;
use Swift\Orm\Behavior\Event\Mapper\Command\OnUpdate;

/**
 * Act based on the life cycle of an entity
 */
#[DI( tags: [ 'orm.lifecycle' ])]
interface LifeCycleInterface {
    
    /**
     * The entity to listen to
     *
     * @return string
     */
    public static function getEntityClass(): string;
    
    /**
     * Fires before creation of an Entity
     *
     * @param \Swift\Orm\Behavior\Event\Mapper\Command\OnCreate $event
     *
     * @return void
     */
    public function onCreate( OnCreate $event ): void;
    
    /**
     * Fires before update of entity
     *
     * @param \Swift\Orm\Behavior\Event\Mapper\Command\OnUpdate $event
     *
     * @return void
     */
    public function onUpdate( OnUpdate $event ): void;
    
    /**
     * Fires before deletion of entity
     *
     * @param \Swift\Orm\Behavior\Event\Mapper\Command\OnDelete $event
     *
     * @return void
     */
    public function onDelete( OnDelete $event ): void;
    
}