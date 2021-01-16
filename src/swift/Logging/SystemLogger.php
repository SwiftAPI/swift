<?php declare(strict_types=1);


namespace Swift\Logging;


use Swift\Events\EventDispatcher;
use Swift\Logging\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

class SystemLogger extends Logger {

    /**
     * SystemLogger constructor.
     *
     * @param EventDispatcher $dispatcher
     */
    public function __construct( EventDispatcher $dispatcher ) {
        $stream = new StreamHandler(INCLUDE_DIR . '/var/system.log', Logger::DEBUG);
        $stream->setFormatter(new LineFormatter());

        parent::__construct($dispatcher, 'system', array($stream));
    }

}