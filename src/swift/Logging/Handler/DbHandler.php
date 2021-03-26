<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Logging\Handler;

use Swift\Kernel\Attributes\Autowire;
use Swift\Logging\Entity\LogEntity;
use Swift\Logging\SystemLogger;
use Monolog\Handler\AbstractProcessingHandler;
use Swift\Model\Exceptions\DatabaseException;

/**
 * Class DBHandler
 * @package Swift\Logging\Handler
 */
#[Autowire]
class DbHandler extends AbstractProcessingHandler {

    /**
     * DBHandler constructor.
     *
     * @param LogEntity $entityLog
     * @param SystemLogger $systemLogger
     */
    public function __construct(
        private LogEntity $entityLog,
        private SystemLogger $systemLogger
    ) {

        parent::__construct();
    }

    /**
     * @param array $record
     */
    protected function write( array $record ): void {
        try {
            $this->entityLog->save(array(
                'channel' => $record['channel'],
                'message' => $record['message'],
                'context' => $record['context'],
                'level' => $record['level'],
                'level_name' => $record['level_name'],
                'datetime' => $record['datetime']->format('Y-m-d H:i:s'),
            ));
        } catch ( DatabaseException $exception ) {
            $this->systemLogger->error('Could not save log record to database in DbHandler', array(
                'record' => $record,
                'exception' => $exception->getMessage(),
            ));
        }
    }
}