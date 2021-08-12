<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Events;

use Swift\Kernel\Attributes\DI;
use Swift\Kernel\KernelDiTags;

/**
 * Interface EventSubscriberInterface
 * @package Swift\Events
 */
#[DI(tags: [KernelDiTags::EVENT_SUBSCRIBER])]
interface EventSubscriberInterface extends \Symfony\Component\EventDispatcher\EventSubscriberInterface {

}