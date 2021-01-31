<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Logging;

use Swift\Events\EventDispatcher;
use Swift\Kernel\Attributes\Autowire;
use Swift\Logging\Event\OnBeforeLoggerHandlersEvent;
use Swift\Logging\Formatter\LineFormatter;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\StreamHandler;
use Swift\Logging\Handler\DBHandler;
use Swift\Logging\Logger;

/**
 * Class UserLogger
 * @package Swift\Logging
 */
#[Autowire]
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