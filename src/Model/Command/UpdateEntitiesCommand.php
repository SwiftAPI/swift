<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Command;

use Swift\Console\Command\AbstractCommand;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\KernelDiTags;
use Swift\Model\EntityInterface;
use Swift\Model\EntityManager;
use Swift\Model\Mapping\Field;
use Swift\Model\Mapping\Index;
use Swift\Model\Query\QueryType;
use Swift\Model\TableFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateEntitiesCommand
 * @package Swift\Database\Command
 */
#[Autowire]
class UpdateEntitiesCommand extends AbstractCommand {

    /** @var EntityInterface[] */
    private array $entities;

    private TableFactory $tableFactory;
    private EntityManager $entityManager;


    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        return 'database:entities:update';
    }



    /**
     * Method to set command configuration
     */
    protected function configure(): void {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription( 'Update all tables by their entities' )

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp( 'This command will update tables from their respective entity' )

            // configure an argument
            ->addOption( '--remove_non_existing', '-r', InputArgument::OPTIONAL, 'Remove database columns and indexes which are not represented in the given entity. Do this by adding the flag -r=1 to this command.' )
            ->addOption( '--drop_table_if_exists', '-d', InputArgument::OPTIONAL, 'Drop the tables if they exist. Do this by adding the flag drop_table to this command.' );
    }

    protected function execute( InputInterface $input, OutputInterface $output ): int {
        $removeNonExistingColumns = (bool) $input->getOption( 'remove_non_existing' );
        $dropTableIfExists        = ! is_null( $input->getOption( 'drop_table_if_exists' ) ) && strtolower( $input->getOption( 'drop_table_if_exists' ) ) === 'drop_table';

        foreach ( $this->entities as $entity ) {
            try {
                $this->updateEntity( $entity, $removeNonExistingColumns, $dropTableIfExists );
                $this->io->newLine( 1 );
            } catch ( \Exception $exception ) {
                $output->writeln( $exception->getMessage() );
            }
        }

        return AbstractCommand::SUCCESS;
    }

    private function updateEntity( EntityInterface $entity, bool $removeNonExistingColumns, bool $dropTableIfExists ): void {
        $io      = $this->io;
        $section = $this->createOutputSection();
        $classMeta = $this->entityManager->getClassMetaDataFactory()->getClassMetaData( $entity::class );
        try {
            $section->writeln( '⏳ <fg=blue;options=bold>Updating:</> "' . $classMeta->getTable()->getDatabaseName() . '" for entity "' . $entity::class . '"' );
            $result = $this->tableFactory->createOrUpdateTable( $entity::class, $removeNonExistingColumns, $dropTableIfExists );
            $section->clear( 1 );
            $section->writeln( '✅ <fg=green;options=bold>Success:</> ' . $this->queryTypeToString( $result->getQueryType() ) . ' "' . $classMeta->getTable()->getDatabaseName() . '" for entity "' . $entity::class . '"' );

            if ( ! empty( $result->getNonExistingColumns() ) ) {
                $this->io->newLine();
                $io->writeln(
                    $removeNonExistingColumns ?
                        '❕ The following non property represented columns were found and removed from the table.' :
                        '❕ The following columns are found in the table, but not represented as properties. Remove them or add them as a property to the entity. You can easily remove them by adding the --remove_non_existing=1 flag to this command'
                );
                $io->listing( array_map( static fn( Field $field ): string => $field->getDatabaseName(), $result->getNonExistingColumns() ) );

            }

            if ( ! empty( $result->getNonExistingIndexes() ) ) {
                $this->io->newLine();
                $io->writeln(
                    $removeNonExistingColumns ?
                        '❕ The following non property represented indexes were found and removed from the table.' :
                        '❕ The following indexes are found in the table, but not represented as properties. Remove them or add them as a property to the entity. You can easily remove them by adding the --remove_non_existing=1 flag to this command'
                );
                $io->listing( array_map( static fn( Index $index ): string => $index->getName(), $result->getNonExistingIndexes() ) );
            }

        } catch ( \Exception $exception ) {
            $section->clear( 1 );
            $section->writeln( '❌ <fg=red;options=bold>Failed:</> Has not updated "' . $classMeta->getTable()->getDatabaseName() . '" for entity "' . $entity::class . '. See: <fg=blue>https://swiftapi.github.io/swift-docs/docs/database/introduction</>"' );
            $io->error( $exception->getMessage() );
        }
    }

    private function queryTypeToString( QueryType $queryType ): string {
        return match ( $queryType->getValue() ) {
            QueryType::CREATE => 'Created',
            QueryType::ALTER => 'Updated',
            QueryType::DELETE => 'Deleted',
            default => 'Performed unrecognized action',
        };
    }


    /**
     * Autowire entities to class
     *
     * @param iterable $entities
     */
    #[Autowire]
    public function setEntities( #[Autowire( tag: KernelDiTags::ENTITY )] iterable $entities ): void {
        foreach ( $entities as $entity ) {
            $this->entities[ $entity::class ] = $entity;
        }
    }

    #[Autowire]
    public function setTableFactory( TableFactory $tableFactory ): void {
        $this->tableFactory = $tableFactory;
    }

    #[Autowire]
    public function setEntityManager( EntityManager $entityManager ): void {
        $this->entityManager = $entityManager;
    }

}