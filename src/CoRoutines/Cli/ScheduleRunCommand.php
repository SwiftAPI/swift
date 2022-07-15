<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\CoRoutines\Cli;

use Swift\CoRoutines\CoRoutineCollection;
use Swift\CoRoutines\ScheduleFactory;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\FileSystem\FileSystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[Autowire]
class ScheduleRunCommand extends \Swift\Runtime\Cli\AbstractRuntimeCommand {
    
    public function __construct(
        private readonly ScheduleFactory $scheduleFactory,
        private readonly CoRoutineCollection $coroutineCollection,
        private readonly FileSystem $fileSystem,
    ) {
        
        parent::__construct();
    }
    
    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        return 'coroutines:run';
    }
    
    /**
     * Configures the current command.
     */
    protected function configure(): void {
        $this
             ->setDescription( 'Starts the event runner.' )
             ->addOption(
                 'task',
                 null,
                 InputOption::VALUE_REQUIRED,
                 'Which task to run. Provide task number from <info>coroutines:list</info> command.',
                 null
             )
             ->setHelp( 'This command start the coroutines runner.' );
    }
    
    /**
     * {@inheritdoc}
     */
    protected function execute( InputInterface $input, OutputInterface $output ): int {
        // If task is given, run single task
        $task = $input->getOption( 'task' );
        if ( $task ) {
            return $this->runSingle( $task );
        }
    
        return $this->runAll();
    }
    
    private function runSingle( string $task ): int {
        $coroutine = $this->coroutineCollection->getCoroutineByIdentifier( $task );
        if ( ! $coroutine ) {
            throw new \InvalidArgumentException( sprintf( 'Could not find coroutine for identifier "%s"', $task ) );
        }
        $coroutine->run( $this->getInputOutputHelper() );
        
        $this->fileSystem->delete( sprintf('/var/coroutines/locks/%s.lock', $task) );
        
        return 0;
    }
    
    private function runAll(): int {
        $this->io->title('Executing coroutine');
        $scheduler = $this->scheduleFactory->createScheduler( $this->io );
    
        $jobs = $scheduler->run();
    
        $finishedJobs = 0;
        while( $finishedJobs < count($jobs)) {
            $finishedJobs = 0;
        
            foreach ($jobs as $job) {
                if ($job->isFinished()) {
                    $job->removeLockFile();
                    $finishedJobs++;
                }
            }
        }
    
        $this->io->success('Completed coroutine');
    
    
        return 0;
    }
    
    
}