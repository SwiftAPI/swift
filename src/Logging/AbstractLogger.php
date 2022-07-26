<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Logging;


use DateTimeZone;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Events\EventDispatcher;
use Swift\Logging\Event\OnBeforeLoggerHandlersEvent;
use Monolog\Handler\HandlerInterface;

/**
 * Class Logger
 * @package Swift\Logging
 */
#[Autowire]
abstract class AbstractLogger extends \Monolog\Logger {
    
    /**
     * @var EventDispatcher $dispatcher
     */
    protected EventDispatcher $dispatcher;
    
    /**
     * Logger constructor.
     *
     * @param string             $name       The logging channel, a simple descriptive name that is attached to all log records
     * @param HandlerInterface[] $handlers   Optional stack of handlers, the first one in the array is called first, etc.
     * @param callable[]         $processors Optional array of processors
     * @param DateTimeZone|null  $timezone   Optional timezone, if not provided date_default_timezone_get() will be used
     */
    public function __construct( string $name = '', array $handlers = [], array $processors = [], ?DateTimeZone $timezone = null ) {
        $this->dispatcher = ( new ServiceLocator() )->get( EventDispatcher::class );
        
        /** @var OnBeforeLoggerHandlersEvent $event */
        $event = $this->dispatcher->dispatch( new OnBeforeLoggerHandlersEvent( AppLogger::class, $handlers ) );
        
        parent::__construct( $name, $event->getHandlers(), $processors, $timezone );
    }
    
}