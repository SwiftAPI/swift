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
use Monolog\Handler\StreamHandler;

/**
 * Class AppLogger
 * @package Swift\Logging
 */
#[Autowire]
class AppLogger extends AbstractLogger {

    /**
     * AppLogger constructor.
     */
    public function __construct() {
        $stream = new StreamHandler(INCLUDE_DIR . '/var/app.log', AbstractLogger::DEBUG);
        $stream->setFormatter(new LineFormatter());

        parent::__construct('app', array($stream));
    }


}