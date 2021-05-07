<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\ORM\Events;

use Swift\Events\EventListenerInterface;
use Swift\Kernel\Attributes\DI;

/**
 * Interface DoctrineEventListener
 * @package Swift\ORM\Events
 */
#[DI(tags: ['doctrine.events.listener'])]
interface DoctrineEventListener extends EventListenerInterface {

}