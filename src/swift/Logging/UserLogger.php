<?php declare(strict_types=1);


namespace Swift\Logging;

use Swift\Events\EventDispatcher;
use Swift\Logging\Event\OnBeforeLoggerHandlersEvent;
use Swift\Logging\Formatter\LineFormatter;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\StreamHandler;
use Swift\Logging\Handler\DBHandler;
use Swift\Logging\Logger;

class UserLogger extends Logger {

    /**
     * UserLogger constructor.
     *
     * @param EventDispatcher $dispatcher
     */
    public function __construct(EventDispatcher $dispatcher) {
        $stream = new StreamHandler(INCLUDE_DIR . '/var/my_app.log', Logger::DEBUG);
        $stream->setFormatter(new LineFormatter());

        $db = new DBHandler(Logger::DEBUG);
        
        parent::__construct($dispatcher, 'users', array($stream, $db));
    }
}