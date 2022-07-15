<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Logging\Handler;

use Swift\Dbal\Exceptions\DatabaseException;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Logging\Entity\LogEntity;
use Swift\Logging\SystemLogger;
use Monolog\Handler\AbstractProcessingHandler;
use Swift\Orm\EntityManagerInterface;

/**
 * Class DBHandler
 * @package Swift\Logging\Handler
 */
#[Autowire]
class DbHandler extends AbstractProcessingHandler {
    
    /**
     * DBHandler constructor.
     *
     * @param \Swift\Orm\EntityManagerInterface $entityManager
     * @param SystemLogger                      $systemLogger
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SystemLogger           $systemLogger
    ) {
        
        parent::__construct();
    }
    
    /**
     * @param array $record
     */
    protected function write( array $record ): void {
        try {
            $log = new LogEntity();
            $log->setChannel( $record[ 'channel' ] );
            $log->setMessage( $record[ 'message' ] );
            $log->setLevel( $record[ 'level' ] );
            $log->setLevelName( $record[ 'level_name' ] );
            $log->setContext( $record[ 'context' ] );
            $log->setDatetime( $record[ 'datetime' ]->format( 'Y-m-d H:i:s' ) );
            
            $this->entityManager->persist( $log );
            $this->entityManager->run();
        } catch ( DatabaseException $exception ) {
            $this->systemLogger->error( 'Could not save log record to database in DbHandler', [
                'record'    => $record,
                'exception' => $exception->getMessage(),
            ] );
        }
    }
}