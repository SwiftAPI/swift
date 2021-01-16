<?php declare(strict_types=1);


namespace Swift\Logging\Handler;

use Exception;
use Swift\Logging\Entity\LogEntity;
use Swift\Logging\SystemLogger;
use Monolog\DateTimeImmutable;
use Monolog\Handler\AbstractProcessingHandler;

class DBHandler extends AbstractProcessingHandler {

    /**
     * DBHandler constructor.
     *
     * @param LogEntity $entityLog
     * @param SystemLogger $systemLogger
     */
    public function __construct(
        private LogEntity $entityLog,
        private SystemLogger $systemLogger ) {

        parent::__construct();
    }

    /**
     * @param array $record
     */
    protected function write( array $record ): void {
        try {
            $dataFormat            = $this->entityLog->getPropertiesAsObject();
            $dataFormat->channel   = $record['channel'];
            $dataFormat->message   = $record['message'];
            $dataFormat->context   = $record['context'];
            $dataFormat->level     = $record['level'];
            $dataFormat->levelName = $record['level_name'];
            /** @var DateTimeImmutable $date */
            $date                 = $record['datetime'];
            $dataFormat->datetime = $date->format( 'Y-m-d H:i:s' );
            $this->entityLog->save($dataFormat);
        } catch ( Exception $exception ) {
            $this->systemLogger->error('Could not save log record to database in DBHandler', array(
                'record' => $record,
                'exception' => $exception->getMessage(),
            ));
        }
    }
}