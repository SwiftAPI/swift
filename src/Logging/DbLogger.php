<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Logging;


use Swift\Kernel\Attributes\Autowire;
use Swift\Logging\Formatter\LineFormatter;
use Swift\Logging\Handler\DbHandler;

/**
 * Class DBLogger
 * @package Swift\Logging
 */
#[Autowire]
class DbLogger extends AbstractLogger {

    /**
     * AppLogger constructor.
     *
     * @param DbHandler $dbHandler
     */
    public function __construct( DbHandler $dbHandler ) {
        $dbHandler->setFormatter(new LineFormatter());

        parent::__construct( 'app', array($dbHandler));
    }


}