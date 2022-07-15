<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Behavior;

use Cycle\ORM\Command\CommandInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Transaction\CommandGenerator;
use Cycle\ORM\Transaction\Tuple;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swift\Orm\Behavior\Dispatcher\Dispatcher;
use Swift\Orm\Behavior\Event\Mapper\Command\OnCreate;
use Swift\Orm\Behavior\Event\Mapper\Command\OnDelete;
use Swift\Orm\Behavior\Event\Mapper\Command\OnUpdate;
use Swift\Orm\Behavior\Dispatcher\ListenerProvider;

final class EventDrivenCommandGenerator extends CommandGenerator {
    
    private EventDispatcherInterface $eventDispatcher;
    private \DateTimeImmutable $timestamp;
    
    public function __construct( SchemaInterface $schema, ContainerInterface $container ) {
        $listenerProvider = new ListenerProvider( $schema, $container );
        
        $this->eventDispatcher = new Dispatcher( $listenerProvider );
    }
    
    protected function storeEntity( ORMInterface $orm, Tuple $tuple, bool $isNew ): ?CommandInterface {
        $role = $tuple->node->getRole();
        $src  = $orm->getSource( $role );
        
        $event = $isNew
            ? new OnCreate( $role, $tuple->mapper, $tuple->entity, $tuple->node, $tuple->state, $src, $this->timestamp )
            : new OnUpdate( $role, $tuple->mapper, $tuple->entity, $tuple->node, $tuple->state, $src, $this->timestamp );
        
        $event->command = parent::storeEntity( $orm, $tuple, $isNew );
        
        $event = $this->eventDispatcher->dispatch( $event );
        
        return $event->command;
    }
    
    /**
     * @param non-empty-string $parentRole
     */
    protected function generateParentStoreCommand(
        ORMInterface $orm,
        Tuple        $tuple,
        string       $parentRole,
        bool         $isNew
    ): ?CommandInterface {
        $mapper = $orm->getMapper( $parentRole );
        $source = $orm->getSource( $parentRole );
        
        $event =
            new OnCreate( $parentRole, $mapper, $tuple->entity, $tuple->node, $tuple->state, $source, $this->timestamp );
        
        $event->command = $isNew
            ? $mapper->queueCreate( $tuple->entity, $tuple->node, $tuple->state )
            : $mapper->queueUpdate( $tuple->entity, $tuple->node, $tuple->state );
        
        $event = $this->eventDispatcher->dispatch( $event );
        
        return $event->command;
    }
    
    protected function deleteEntity( ORMInterface $orm, Tuple $tuple ): ?CommandInterface {
        $role   = $tuple->node->getRole();
        $source = $orm->getSource( $role );
        
        $event = new OnDelete(
            $role,
            $tuple->mapper,
            $tuple->entity,
            $tuple->node,
            $tuple->state,
            $source,
            $this->timestamp
        );
        
        $event->command = parent::deleteEntity( $orm, $tuple );
        
        $event = $this->eventDispatcher->dispatch( $event );
        
        return $event->command;
    }
    
    public function generateStoreCommand( ORMInterface $orm, Tuple $tuple ): ?CommandInterface {
        $this->timestamp = new \DateTimeImmutable();
        
        $tuple->state->setData( $this->resolveEnums( $tuple->state->getData() ) );
        
        $command = parent::generateStoreCommand( $orm, $tuple );
        
        unset( $this->timestamp );
        
        return $command;
    }
    
    public function generateDeleteCommand( ORMInterface $orm, Tuple $tuple ): ?CommandInterface {
        $this->timestamp = new \DateTimeImmutable();
        
        $command = parent::generateDeleteCommand( $orm, $tuple );
        
        unset( $this->timestamp );
        
        return $command;
    }
    
    protected function resolveEnums( array $data ): array {
        foreach ( $data as $key => $value ) {
            if ( ! is_object( $value ) ) {
                continue;
            }
            
            if (enum_exists( get_class( $value ) )) {
                $data[ $key ] = $value->value ?? $value->name;
            }
        }
        
        return $data;
    }
    
}
