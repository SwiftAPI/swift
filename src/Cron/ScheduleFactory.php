<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Cron;

use Swift\Console\Style\ConsoleStyle;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Process\Process;

#[Autowire]
class ScheduleFactory {
    
    public function __construct(
        private readonly CronCollection $cronCollection,
    ) {
    }
    
    public function createScheduler( ?ConsoleStyle $consoleStyle = null ): Scheduler {
        $scheduler = new Scheduler();
        
        foreach ($this->cronCollection->getCrons() as $cron) {
            $job = $scheduler->process(
                new Process(
                    [PHP_BINARY, INCLUDE_DIR . '/bin/console', 'cron:run', '--task', $cron->getIdentifier() ],
                    null,
                    null,
                    null,
                    3600
                ),
                [],
                $cron->getIdentifier()
            );
            $cron->configure( $job );
            $job->setDescription( $cron->getDescription() );
        }
        
        return $scheduler;
    }
    
}