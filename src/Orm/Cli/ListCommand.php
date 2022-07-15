<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Cli;


use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Swift\Console\Command\AbstractCommand;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Orm\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[Autowire]
class ListCommand extends \Swift\Console\Command\AbstractCommand {
    
    public function __construct(
        private readonly Factory $ormFactory,
    ) {
        parent::__construct();
    }
    
    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        return 'orm:list';
    }
    
    public function getDescription(): string {
        return 'List of all available entities and their tables';
    }
    
    public function getHelp(): string {
        return 'List of all available entities and their tables';
    }
    
    public function execute( InputInterface $input, OutputInterface $output ): int {
        $orm = $this->ormFactory->getOrm();
        $tableHeaders = [
            'Role:',
            'Class:',
            'Table:',
            'Repository:',
            'Fields:',
            'Relations:',
        ];
        $tableRows = [];
    
        if ($orm->getSchema()->getRoles() === []) {
            $this->io->writeln('<info>No entity were found</info>');
        
            return AbstractCommand::SUCCESS;
        }
    
        foreach ($orm->getSchema()->getRoles() as $role) {
            $tableRows[] = $this->describeEntity($orm->getSchema(), $role);
        }
    
        $this->io->table( $tableHeaders, $tableRows );
        
        return AbstractCommand::SUCCESS;
    }
    
    /**
     * @param SchemaInterface $schema
     * @param string          $role
     *
     * @return array
     */
    protected function describeEntity( SchemaInterface $schema, string $role ): array {
        return [
            $role,
            $schema->define( $role, Schema::ENTITY ),
            $schema->define( $role, Schema::TABLE ),
            $schema->define( $role, Schema::REPOSITORY ),
            implode( ', ', array_keys( $schema->define( $role, Schema::COLUMNS ) ) ),
            implode( ', ', array_keys( $schema->define( $role, Schema::RELATIONS ) ) ),
        ];
    }
    
}