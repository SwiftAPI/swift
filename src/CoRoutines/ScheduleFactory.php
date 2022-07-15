<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\CoRoutines;

use Swift\Console\Style\ConsoleStyle;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Process\Process;

#[Autowire]
class ScheduleFactory {
    
    public function __construct(
        private readonly CoRoutineCollection $coroutineCollection,
    ) {
    }
    
    public function createScheduler( ?ConsoleStyle $consoleStyle = null ): Scheduler {
        $scheduler = new Scheduler();
        
        foreach ($this->coroutineCollection->getCoroutines() as $coroutine) {
            $job = $scheduler->process( new Process([PHP_BINARY, SWIFT_ROOT . '/Runtime/Server/server-bootstrap.php', 'coroutines:run', '--task', $coroutine->getIdentifier() ]), [], $coroutine->getIdentifier() );
            $coroutine->configure( $job );
            $job->setDescription( $coroutine->getDescription() );
        }
        
        return $scheduler;
    }
    
}