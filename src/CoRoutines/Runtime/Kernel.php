<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\CoRoutines\Runtime;


use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Filesystem\Node\FileInterface;
use React\Promise\Promise;
use Swift\Configuration\ConfigurationInterface;
use Swift\Configuration\Utils;
use Swift\Console\Style\ConsoleStyle;
use Swift\CoRoutines\CoRoutineCollection;
use Swift\CoRoutines\ScheduleFactory;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Runtime\Cli\AbstractRuntimeCommand;

#[Autowire]
final class Kernel implements \Swift\Runtime\RuntimeKernelInterface {
    
    public function __construct(
        private readonly ConfigurationInterface $configuration,
        private readonly ScheduleFactory        $scheduleFactory,
        private readonly CoRoutineCollection    $coroutineCollection,
    ) {
    }
    
    public function run( ConsoleStyle $io, AbstractRuntimeCommand $command, LoopInterface $loop ): int {
        $io->title( 'Coroutines' );
        $io->writeln( sprintf( '<info>Scheduled %d coroutines</info>', count( $this->coroutineCollection->getCoroutines() ) ) );
        $io->newLine( 3 );
        
        $loop->addPeriodicTimer( 60, function () use ( $io ) {
            $scheduler = $this->scheduleFactory->createScheduler( $io );
            $jobs      = $scheduler->run();
            
            if ( $this->configuration->get( 'coroutines.run_in_background', 'runtime' ) ) {
                return;
            }
            
            $finishedJobs = 0;
            while ( $finishedJobs < count( $jobs ) ) {
                $finishedJobs = 0;
                
                foreach ( $jobs as $job ) {
                    if ( $job->isFinished() ) {
                        $job->removeLockFile();
                        $finishedJobs ++;
                    }
                }
            }
        } );
        
        return 0;
    }
    
    public function isDebug(): bool {
        return Utils::isDebug( $this->configuration );
    }
    
    public function finalize(): void {
    
    }
    
    
}