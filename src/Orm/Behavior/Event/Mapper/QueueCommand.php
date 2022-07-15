<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Behavior\Event\Mapper;

use Cycle\ORM\Command\CommandInterface;
use Swift\Orm\Behavior\Event\MapperEvent;

abstract class QueueCommand extends MapperEvent {
    public ?CommandInterface $command;
}
