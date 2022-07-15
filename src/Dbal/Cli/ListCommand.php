<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Dbal\Cli;


use Cycle\Database\Database;
use Cycle\Database\Driver\Driver;
use Swift\Console\Command\AbstractCommand;
use Swift\Dbal\DbalFactory;
use Swift\DependencyInjection\Attributes\Autowire;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Get list of available databases, their tables and records count
 */
#[Autowire]
class ListCommand extends \Swift\Console\Command\AbstractCommand {
    
    /**
     * No information available placeholder.
     */
    private const SKIP = '<comment>---</comment>';
    
    public function __construct(
        private readonly DbalFactory $dbalFactory,
    ) {
        parent::__construct();
    }
    
    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        return 'dbal:list';
    }
    
    public function getDescription(): string {
        return 'Get list of available databases, their tables and records count';
    }
    
    public function getHelp(): string {
        return 'Get list of available databases, their tables and records count';
    }
    
    protected function configure(): void {
        $this
            ->addArgument( 'db', InputArgument::OPTIONAL, 'Database name' )
        ;
    }
    
    public function execute( InputInterface $input, OutputInterface $output ): int {
        if ($input->getArgument('db')) {
            $databases = [$input->getArgument('db')];
        } else {
            $databases = array_keys($this->dbalFactory->getDatabaseConfig()->getDatabases());
        }
    
        if (empty($databases)) {
            $this->io->writeln('<fg=red>No databases found.</fg=red>');
        
            return AbstractCommand::SUCCESS;
        }
    
        
        $tableHeaders = [
            'Name (ID):',
            'Database:',
            'Driver:',
            'Prefix:',
            'Status:',
            'Tables:',
            'Count Records:',
        ];
        $tableColumns = [];
    
        foreach ($databases as $database) {
            $database = $this->dbalFactory->getDatabaseManager()->database($database);
        
            /** @var Driver $driver */
            $driver = $database->getDriver();
        
            $header = [
                $database->getName(),
                $driver->getSource(),
                $driver->getType(),
                $database->getPrefix() ?: self::SKIP,
            ];
        
            try {
                $driver->connect();
            } catch (\Exception $exception) {
                $tableColumns[] = $this->renderException($header, $exception);
            
                if ($database->getName() !== end($databases)) {
                    $tableColumns[] = new TableSeparator();
                }
            
                continue;
            }
        
            $header[] = '<info>connected</info>';
            $tableColumns = [...$tableColumns, ...$this->renderTables($header, $database)];
            if ($database->getName() !== end($databases)) {
                $tableColumns[] = new TableSeparator();
            }
        }
    
        $this->io->table($tableHeaders, $tableColumns);
        
        return AbstractCommand::SUCCESS;
    }
    
    /**
     * @param array      $header
     * @param \Throwable $exception
     *
     * @return array
     */
    private function renderException( array $header, \Throwable $exception ): array {
        return array_merge(
            $header,
            [
                "<fg=red>{$exception->getMessage()}</fg=red>",
                self::SKIP,
                self::SKIP,
            ]
        );
    }
    
    /**
     * @param array    $header
     * @param Database $database
     *
     * @return array
     */
    private function renderTables( array $header, Database $database ): array {
        $cols = [];
        foreach ( $database->getTables() as $table ) {
            $cols[] = array_merge(
                $header,
                [ $table->getName(), number_format( $table->count() ) ]
            );
            $header = [ '', '', '', '', '' ];
        }
        
        if (!empty($header[1])) {
            $cols[] = array_merge( $header, [ 'no tables', 'no records' ] );
        }
        
        return $cols;
    }
    
}