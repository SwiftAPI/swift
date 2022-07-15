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
use Swift\FileSystem\FileSystem;
use Swift\Process\Process;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process as ProcessAlias;

final class Job extends \GO\Job {
    
    use \Go\Traits\Interval,
        \Go\Traits\Mailer;
    
    /**
     * Defines if the job should run in background.
     *
     * @var bool
     */
    private bool $runInBackground = true;
    
    /**
     * Creation time.
     *
     * @var \DateTime
     */
    private \DateTime $creationTime;
    
    /**
     * Job schedule time.
     *
     * @var \Cron\CronExpression
     */
    private \Cron\CronExpression $executionTime;
    
    /**
     * Job schedule year.
     *
     * @var string|null
     */
    private ?string $executionYear = null;
    
    /**
     * Temporary directory path for
     * lock files to prevent overlapping.
     *
     * @var string
     */
    private string $tempDir;
    
    /**
     * Path to the lock file.
     *
     * @var string|null
     */
    private ?string $lockFile = null;
    
    /**
     * This could prevent the job to run.
     * If true, the job will run (if due).
     *
     * @var bool
     */
    private bool $truthTest = true;
    
    /**
     * The output of the executed job.
     *
     * @var mixed
     */
    private mixed $output;
    
    /**
     * The return code of the executed job.
     *
     * @var int
     */
    private int $returnCode = 0;
    
    /**
     * Files to write the output of the job.
     *
     * @var array
     */
    private array $outputTo = [];
    
    /**
     * Email addresses where the output should be sent to.
     *
     * @var array
     */
    private array $emailTo = [];
    
    /**
     * Configuration for email sending.
     *
     * @var array
     */
    private $emailConfig = [];
    
    /**
     * A function to execute before the job is executed.
     *
     * @var callable
     */
    private $before;
    
    /**
     * A function to execute after the job is executed.
     *
     * @var callable
     */
    private $after;
    
    /**
     * A function to ignore an overlapping job.
     * If true, the job will run also if it's overlapping.
     *
     * @var callable
     */
    private $whenOverlapping;
    
    /**
     * @var string
     */
    private string $outputMode;
    
    private string $description;
    
    private readonly FileSystem $fileSystem;
    
    public function __construct(
        private readonly mixed $command,
        private readonly array $args,
        private readonly string $id,
    ) {
        $this->creationTime = new \DateTime( 'now' );
        
        // initialize the directory path for lock files
        $this->tempDir = '/var/coroutines/locks';
        
        $this->fileSystem = new FileSystem();
        if(!$this->fileSystem->dirExists($this->tempDir)) {
            $this->fileSystem->createDirectory($this->tempDir);
        }
        
    }
    
    
    /**
     * @return string
     */
    public function getDescription(): string {
        return $this->description;
    }
    
    /**
     * @param string $description
     */
    public function setDescription( string $description ): void {
        $this->description = $description;
    }
    
    /**
     * Get the Job id.
     *
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }
    
    /**
     * Check if the Job is due to run.
     * It accepts as input a DateTime used to check if
     * the job is due. Defaults to job creation time.
     * It also defaults the execution time if not previously defined.
     *
     * @param \DateTime $date
     *
     * @return bool
     */
    public function isDue( \DateTime $date = null ): bool {
        // The execution time is being defaulted if not defined
        if ( ! $this->executionTime ) {
            $this->at( '* * * * *' );
        }
        
        $date = $date ?? $this->creationTime;
        
        if ( $this->executionYear && $this->executionYear !== $date->format( 'Y' ) ) {
            return false;
        }
        
        return $this->executionTime->isDue( $date );
    }
    
    /**
     * Check if the Job is overlapping.
     *
     * @return bool
     */
    public function isOverlapping(): bool {
        return $this->lockFile &&
               $this->fileSystem->fileExists( $this->lockFile ) &&
               call_user_func( $this->whenOverlapping, $this->fileSystem->lastModified($this->lockFile) ) === false;
    }
    
    /**
     * Force the Job to run in foreground.
     *
     * @return self
     */
    public function inForeground(): Job {
        $this->runInBackground = false;
        
        return $this;
    }
    
    /**
     * Check if the Job can run in background.
     *
     * @return bool
     */
    public function canRunInBackground(): bool {
        if ( is_callable( $this->command ) || is_a( $this->command, Process::class ) || $this->runInBackground === false ) {
            return false;
        }
        
        return true;
    }
    
    /**
     * This will prevent the Job from overlapping.
     * It prevents another instance of the same Job of
     * being executed if the previous is still running.
     * The job id is used as a filename for the lock file.
     *
     * @param null          $tempDir         The directory path for the lock files
     * @param callable|null $whenOverlapping A callback to ignore job overlapping
     *
     * @return self
     */
    public function onlyOne( $tempDir = null, callable $whenOverlapping = null ): Job {
        if ( $tempDir === null || ! is_dir( $tempDir ) ) {
            $tempDir = $this->tempDir;
        }
        
        $this->lockFile = implode( '/', [
            trim( $tempDir ),
            trim( $this->id ) . '.lock',
        ] );
        
        if ( $whenOverlapping ) {
            $this->whenOverlapping = $whenOverlapping;
        } else {
            $this->whenOverlapping = static function(): bool {
                return false;
            };
        }
        
        return $this;
    }
    
    /**
     * Compile the Job command.
     *
     * @return string|callable|\Swift\Process\Process
     */
    public function compile(): string|callable|Process {
        $compiled = $this->command;
        
        // If callable, return the function itself
        if ( is_callable( $compiled ) ) {
            return $compiled;
        }
        
        if ( is_a( $this->command, Process::class ) ) {
            return $compiled;
        }
        
        // Augment with any supplied arguments
        foreach ( $this->args as $key => $value ) {
            $compiled .= ' ' . escapeshellarg( (string) $key );
            if ( $value !== null ) {
                $compiled .= ' ' . escapeshellarg( (string) $value );
                $compiled .= ' ' . $value;
            }
        }
        
        // Add the boilerplate to redirect the output to file/s
        if ( count( $this->outputTo ) > 0 ) {
            $compiled .= ' | tee ';
            $compiled .= $this->outputMode === 'a' ? '-a ' : '';
            foreach ( $this->outputTo as $file ) {
                $compiled .= $file . ' ';
            }
            
            $compiled = trim( $compiled );
        }
        
        // Add boilerplate to remove lockfile after execution
        if ( $this->lockFile ) {
            $compiled .= '; rm ' . $this->lockFile;
        }
        
        // Add boilerplate to run in background
        if ( $this->canRunInBackground() ) {
            // Parentheses are need execute the chain of commands in a subshell
            // that can then run in background
            $compiled = '(' . $compiled . ') > /dev/null 2>&1 &';
        }
        
        return trim( $compiled );
    }
    
    /**
     * Configure the job.
     *
     * @param array $config
     *
     * @return self
     */
    public function configure( array $config = [] ): Job {
        if ( isset( $config[ 'email' ] ) ) {
            if ( ! is_array( $config[ 'email' ] ) ) {
                throw new \InvalidArgumentException( 'Email configuration should be an array.' );
            }
            $this->emailConfig = $config[ 'email' ];
        }
        
        // Check if config has defined a tempDir
        if ( isset( $config[ 'tempDir' ] ) && is_dir( $config[ 'tempDir' ] ) ) {
            $this->tempDir = $config[ 'tempDir' ];
        }
        
        return $this;
    }
    
    /**
     * Truth test to define if the job should run if due.
     *
     * @param callable $fn
     *
     * @return self
     */
    public function when( callable $fn ): Job {
        $this->truthTest = $fn();
        
        return $this;
    }
    
    /**
     * Run the job.
     *
     * @return bool
     */
    public function run(): bool {
        // If the truthTest failed, don't run
        if ( $this->truthTest !== true ) {
            return false;
        }
        
        // If overlapping, don't run
        if ( $this->isOverlapping() ) {
            return false;
        }
        
        $compiled = $this->compile();
        
        // Write lock file if necessary
        $this->createLockFile();
        
        if ( is_callable( $this->before ) ) {
            call_user_func( $this->before );
        }
        
        if ( is_callable( $compiled ) ) {
            $this->output = $this->exec( $compiled );
        } else {
            $compiled->start( function ( $type, $buffer ): void {
                $style = new ConsoleStyle( new ArgvInput(), new ConsoleOutput() );
                if ( ProcessAlias::ERR === $type ) {
                    $style->writeln(sprintf('<fg=red>[COROUTINE] [%s]</> %s', $this->getId(), $buffer));
                } else {
                    $style->writeln(sprintf('<fg=blue>[COROUTINE] [%s]</> %s', $this->getId(), $buffer));
                }
            } );
            
        }
        
        $this->finalise();
        
        return true;
    }
    
    public function isFinished(): bool {
        if ( ! is_a( $this->command, Process::class ) ) {
            return true;
        }
        
        return $this->command->isStarted() && $this->command->isSuccessful();
    }
    
    public function __toString(): string {
        return $this->getId();
    }
    
    /**
     * Create the job lock file.
     *
     * @param mixed|null $content
     *
     * @return void
     */
    private function createLockFile( mixed $content = null ): void {
        if ( $this->lockFile ) {
            if ( $content === null || ! is_string( $content ) ) {
                $content = $this->getId();
            }
            
            $this->fileSystem->write( $this->lockFile, $content );
        }
    }
    
    /**
     * Remove the job lock file.
     *
     * @return void
     */
    public function removeLockFile(): void {
        if ( $this->lockFile && $this->fileSystem->fileExists( $this->lockFile ) ) {
            $this->fileSystem->delete( $this->lockFile );
        }
    }
    
    /**
     * Execute a callable job.
     *
     * @param callable $fn
     *
     * @return string
     * @throws \Exception
     */
    private function exec( callable $fn ): string {
        ob_start();
        
        try {
            $returnData = call_user_func_array( $fn, $this->args );
        } catch ( \Exception $e ) {
            ob_end_clean();
            throw $e;
        }
        
        $outputBuffer = ob_get_clean();
        
        foreach ( $this->outputTo as $filename ) {
            if ( $outputBuffer ) {
                file_put_contents( $filename, $outputBuffer, $this->outputMode === 'a' ? FILE_APPEND : 0 );
            }
            
            if ( $returnData ) {
                file_put_contents( $filename, $returnData, FILE_APPEND );
            }
        }
        
        $this->removeLockFile();
        
        return $outputBuffer . ( is_string( $returnData ) ? $returnData : '' );
    }
    
    /**
     * Set the file/s where to write the output of the job.
     *
     * @param string|array $filename
     * @param bool         $append
     *
     * @return self
     */
    public function output( $filename, $append = false ): Job {
        $this->outputTo   = is_array( $filename ) ? $filename : [ $filename ];
        $this->outputMode = $append === false ? 'w' : 'a';
        
        return $this;
    }
    
    /**
     * Get the job output.
     *
     * @return mixed
     */
    public function getOutput(): mixed {
        return $this->output;
    }
    
    /**
     * Set the emails where the output should be sent to.
     * The Job should be set to write output to a file
     * for this to work.
     *
     * @param string|array $email
     *
     * @return self
     */
    public function email( $email ): Job {
        if ( ! is_string( $email ) && ! is_array( $email ) ) {
            throw new \InvalidArgumentException( 'The email can be only string or array' );
        }
        
        $this->emailTo = is_array( $email ) ? $email : [ $email ];
        
        // Force the job to run in foreground
        $this->inForeground();
        
        return $this;
    }
    
    /**
     * Finilise the job after execution.
     *
     * @return void
     */
    private function finalise(): void {
        // Send output to email
        $this->emailOutput();
        
        // Call any callback defined
        if ( is_callable( $this->after ) ) {
            call_user_func( $this->after, $this->output, $this->returnCode );
        }
    }
    
    /**
     * Email the output of the job, if any.
     *
     * @return bool
     */
    private function emailOutput(): bool {
        if ( ! count( $this->outputTo ) || ! count( $this->emailTo ) ) {
            return false;
        }
        
        if ( isset( $this->emailConfig[ 'ignore_empty_output' ] ) &&
             $this->emailConfig[ 'ignore_empty_output' ] === true &&
             empty( $this->output )
        ) {
            return false;
        }
        
        $this->sendToEmails( $this->outputTo );
        
        return true;
    }
    
    /**
     * Set function to be called before job execution
     * Job object is injected as a parameter to callable function.
     *
     * @param callable $fn
     *
     * @return self
     */
    public function before( callable $fn ): Job {
        $this->before = $fn;
        
        return $this;
    }
    
    /**
     * Set a function to be called after job execution.
     * By default this will force the job to run in foreground
     * because the output is injected as a parameter of this
     * function, but it could be avoided by passing true as a
     * second parameter. The job will run in background if it
     * meets all the other criteria.
     *
     * @param callable $fn
     * @param bool     $runInBackground
     *
     * @return self
     */
    public function then( callable $fn, $runInBackground = false ): Job {
        $this->after = $fn;
        
        // Force the job to run in foreground
        if ( $runInBackground === false ) {
            $this->inForeground();
        }
        
        return $this;
    }
    
}