<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Events;

use Swift\DependencyInjection\Attributes\DI;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class AbstractEvent
 * @package Swift\Events
 */
#[DI(tags: ['events.event'], autowire: false)]
abstract class AbstractEvent extends Event {

    protected static string $eventDescription = '';
    protected static string $eventLongDescription = '';

    public static function getEventDescription(): string {
        return self::$eventDescription;
    }

    public static function getEventLongDescription(): string {
        return self::$eventLongDescription ?? self::$eventDescription;
    }


}