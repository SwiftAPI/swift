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
use Cycle\Database\Exception\DBALException;
use Cycle\Database\Injection\FragmentInterface;
use Cycle\Database\Schema\AbstractColumn;
use Cycle\Database\Schema\AbstractTable;
use DateTimeInterface;
use Swift\Console\Command\AbstractCommand;
use Swift\Dbal\DbalFactory;
use Swift\DependencyInjection\Attributes\Autowire;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Describe table schema of specific database
 */
#[Autowire]
class TableCommand extends \Swift\Console\Command\AbstractCommand {
    
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
        return 'dbal:table';
    }
    
    public function getDescription(): string {
        return 'Describe table schema of specific database';
    }
    
    public function getHelp(): string {
        return 'Describe table schema of specific database';
    }
    
    protected function configure(): void {
        $this
            ->addArgument( 'table', InputArgument::REQUIRED, 'Table name' )
            ->addOption( 'database', 'db', InputOption::VALUE_OPTIONAL, 'Source database', null );
    }
    
    public function execute( InputInterface $input, OutputInterface $output ): int {
        $database = $this->dbalFactory->getDatabaseManager()->database($input->getOption('database'));
        $schema = $database->table($input->getArgument('table'))->getSchema();
    
        if (!$schema->exists()) {
            throw new DBALException(
                "Table {$database->getName()}.{$input->getArgument('table')} does not exists."
            );
        }
    
        $this->io->writeln(
            sprintf(
                "\n<fg=cyan>Columns of </fg=cyan><comment>%s.%s</comment>:\n",
                $database->getName(),
                $input->getArgument('table')
            )
        );
    
        $this->describeColumns($schema);
    
        if (!empty($indexes = $schema->getIndexes())) {
            $this->describeIndexes($database, $indexes);
        }
    
        if (!empty($foreignKeys = $schema->getForeignKeys())) {
            $this->describeForeignKeys($database, $foreignKeys);
        }
    
        $this->io->newLine();
        
        return AbstractCommand::SUCCESS;
    }
    
    /**
     * @param AbstractTable $schema
     */
    protected function describeColumns( AbstractTable $schema ): void {
        $tableHeaders = [
            'Column:',
            'Database Type:',
            'Abstract Type:',
            'PHP Type:',
            'Default Value:',
        ];
        $tableColumns = [];
        
        foreach ( $schema->getColumns() as $column ) {
            $name = $column->getName();
            
            if ( in_array( $column->getName(), $schema->getPrimaryKeys(), true ) ) {
                $name = "<fg=magenta>{$name}</fg=magenta>";
            }
            
            $tableColumns[] = [
                $name,
                $this->describeType( $column ),
                $this->describeAbstractType( $column ),
                $column->getType(),
                $this->describeDefaultValue( $column ) ?: self::SKIP,
            ];
        }
        
        $this->io->table( $tableHeaders, $tableColumns );
    }
    
    /**
     * @param Database $database
     * @param array    $indexes
     */
    protected function describeIndexes( Database $database, array $indexes ): void {
        $this->io->writeln(
            sprintf(
                "\n<fg=cyan>Indexes of </fg=cyan><comment>%s.%s</comment>:\n",
                $database->getName(),
                $this->input->getArgument( 'table' )
            )
        );
        
        $tableHeaders = [ 'Name:', 'Type:', 'Columns:' ];
        $tableColumns = [];
        foreach ( $indexes as $index ) {
            $tableColumns[] = [
                $index->getName(),
                $index->isUnique() ? 'UNIQUE INDEX' : 'INDEX',
                implode( ', ', $index->getColumns() ),
            ];
        }
        
        $this->io->table( $tableHeaders, $tableColumns );
    }
    
    /**
     * @param Database $database
     * @param array    $foreignKeys
     */
    protected function describeForeignKeys( Database $database, array $foreignKeys ): void {
        $this->io->writeln(
            sprintf(
                "\n<fg=cyan>Foreign Keys of </fg=cyan><comment>%s.%s</comment>:\n",
                $database->getName(),
                $this->input->getArgument( 'table' )
            )
        );
        $tableHeaders = [
            'Name:',
            'Column:',
            'Foreign Table:',
            'Foreign Column:',
            'On Delete:',
            'On Update:',
        ];
        $tableColumns = [];
        
        foreach ( $foreignKeys as $reference ) {
            $tableColumns[] = [
                $reference->getName(),
                $reference->getColumn(),
                $reference->getForeignTable(),
                $reference->getForeignKey(),
                $reference->getDeleteRule(),
                $reference->getUpdateRule(),
            ];
        }
        
        $this->io->table( $tableHeaders, $tableColumns );
    }
    
    /**
     * @param AbstractColumn $column
     *
     * @return string|null
     */
    protected function describeDefaultValue( AbstractColumn $column ): ?string {
        $defaultValue = $column->getDefaultValue();
        
        if ( $defaultValue instanceof FragmentInterface ) {
            $defaultValue = "<info>{$defaultValue}</info>";
        }
        
        if ( $defaultValue instanceof DateTimeInterface ) {
            $defaultValue = $defaultValue->format( 'c' );
        }
        
        return $defaultValue;
    }
    
    /**
     * @param AbstractColumn $column
     *
     * @return string
     */
    private function describeType( AbstractColumn $column ): string {
        $type = $column->getType();
        
        $abstractType = $column->getAbstractType();
        
        if ( $column->getSize() ) {
            $type .= " ({$column->getSize()})";
        }
        
        if ( $abstractType === 'decimal' ) {
            $type .= " ({$column->getPrecision()}, {$column->getScale()})";
        }
        
        return $type;
    }
    
    /**
     * @param AbstractColumn $column
     *
     * @return string
     */
    private function describeAbstractType( AbstractColumn $column ): string {
        $abstractType = $column->getAbstractType();
        
        if ( in_array( $abstractType, [ 'primary', 'bigPrimary' ] ) ) {
            $abstractType = "<fg=magenta>{$abstractType}</fg=magenta>";
        }
        
        return $abstractType;
    }
    
}