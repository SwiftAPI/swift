<?php declare(strict_types=1);


namespace Swift\Logging;


use Swift\Events\EventDispatcher;
use Swift\Logging\Formatter\LineFormatter;
use Swift\Logging\Handler\DBHandler;
use Monolog\Handler\StreamHandler;

class DBLogger extends Logger {

    /**
     * AppLogger constructor.
     *
     * @param EventDispatcher $dispatcher
     */
    public function __construct( EventDispatcher $dispatcher, DBHandler $dbHandler ) {
        $dbHandler->setFormatter(new LineFormatter());

        parent::__construct($dispatcher, 'app', array($dbHandler));
    }


}