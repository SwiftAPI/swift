<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Behavior\Event;

use Cycle\ORM\Heap\Node;
use Cycle\ORM\Heap\State;
use Cycle\ORM\MapperInterface;
use Cycle\ORM\Select\SourceInterface;

/**
 * @internal
 *
 * Don't listen to this event
 */
abstract class MapperEvent extends \Cycle\ORM\Entity\Behavior\Event\MapperEvent {
    
    public function __construct(
        public string             $role,
        public MapperInterface    $mapper,
        public object             $entity,
        public Node               $node,
        public State              $state,
        public SourceInterface    $source,
        public \DateTimeImmutable $timestamp
    ) {
    }
    
}
