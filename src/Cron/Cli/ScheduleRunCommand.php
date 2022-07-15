<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Cron\Cli;

use Swift\Cron\CronCollection;
use Swift\Cron\ScheduleFactory;
use Swift\DependencyInjection\Attributes\Autowire;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[Autowire]
class ScheduleRunCommand extends \Swift\Console\Command\AbstractCommand {
    
    public function __construct(
        private readonly ScheduleFactory $scheduleFactory,
        private readonly CronCollection $cronCollection,
    ) {
        
        parent::__construct();
    }
    
    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        return 'cron:run';
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
                 'Which task to run. Provide task number from <info>cron:list</info> command.',
                 null
             )
             ->setHelp( 'This command starts the cron event runner.' );
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
        $this->io->writeln( sprintf( 'Running schedule: %s', $task ) );
        $cron = $this->cronCollection->getCronByIdentifier( $task );
        if ( ! $cron ) {
            throw new \InvalidArgumentException( sprintf( 'Could not find cron for identifier "%s"', $task ) );
        }
        $cron->run( $this->getInputOutputHelper() );
    
        return 0;
    }
    
    private function runAll(): int {
        $this->io->title('Executing cron');
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
    
        $this->io->success('Completed cron');
    
    
        return 0;
    }
    
    
}