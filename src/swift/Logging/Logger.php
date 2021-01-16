<?php declare(strict_types=1);

namespace Swift\Logging;


use Swift\Events\EventDispatcher;
use Swift\Logging\Event\OnBeforeLoggerHandlersEvent;
use Swift\Logging\Handler\DBHandler;
use Monolog\Handler\HandlerInterface;

class Logger extends \Monolog\Logger {

    /**
     * @var EventDispatcher $dispatcher
     */
    private $dispatcher;

    /**
     * Logger constructor.
     *
     * @param EventDispatcher    $dispatcher
     * @param string             $name       The logging channel, a simple descriptive name that is attached to all log records
     * @param HandlerInterface[] $handlers   Optional stack of handlers, the first one in the array is called first, etc.
     * @param callable[]         $processors Optional array of processors
     * @param DateTimeZone|null  $timezone   Optional timezone, if not provided date_default_timezone_get() will be used
     */
    public function __construct( EventDispatcher $dispatcher, string $name = '', array $handlers = array(), array $processors =  array(), ?DateTimeZone $timezone = null ) {
        $this->dispatcher = $dispatcher;

        /** @var OnBeforeLoggerHandlersEvent $event */
        $event = $this->dispatcher->dispatch(new OnBeforeLoggerHandlersEvent(UserLogger::class, $handlers));

        parent::__construct($name, $event->getHandlers(), $processors, $timezone);
    }


}