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
use Swift\Logging\Formatter\LineFormatter;
use Swift\Logging\Handler\DBHandler;
use Monolog\Handler\StreamHandler;

/**
 * Class DBLogger
 * @package Swift\Logging
 */
#[Autowire]
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