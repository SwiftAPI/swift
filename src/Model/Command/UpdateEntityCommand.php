<?php declare(strict_types=1);

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
use Swift\Model\Entity;
use Swift\Model\Mapping\Field;
use Swift\Model\Mapping\Index;
use Swift\Model\TableFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class UpdateEntityCommand
 * @package Swift\Database\Command
 */
#[Autowire]
class UpdateEntityCommand extends AbstractCommand {

    /** @var Entity[] */
    private array $entities;

    private TableFactory $tableFactory;

    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        return 'database:entity:update';
    }


    /**
	 * Method to set command configuration
	 */
	protected function configure(): void {
		$this
			// the short description shown while running "php bin/console list"
			->setDescription('Update a table from an entity')

			// the full command description shown when running the command with
			// the "--help" option
			->setHelp('This command will update a table from a given entity')

			// configure an argument
			->addOption('--entity', null, InputArgument::REQUIRED, 'Entity fully qualified class name')
			->addOption('--remove_non_existing', '-r', InputArgument::OPTIONAL, 'Remove database columns which are not represented in the given entity')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$entityName = $input->getOption('entity');
		$removeNonExistingColumns   = (bool) $input->getOption('remove_non_existing');

		if (!$entityName) {
			$this->io->writeln('<fg=red>No entity given</>');
			return 0;
		}

		if (!array_key_exists($entityName, $this->entities)) {
			$this->io->writeln('<fg=red>Entity ' . $entityName . ' is not found. Is it registered in the Container?</>');
			return 0;
		}

		$entity = $this->entities[$entityName];
		if (!is_subclass_of($entity, Entity::class)) {
			$this->io->writeln('<fg=red>' . $entityName . ' is not a valid entity</>');
			return 0;
		}

		try {
			$this->io->write('<fg=blue>Update table ' . $entity->getTableName() . ' for entity ' . $entityName . '</>');
			$result = $this->tableFactory->createOrUpdateTable( $entity::class, $removeNonExistingColumns, false );
			$this->io->writeln(': success');
			$this->io->newLine();

            if ( ! empty( $result->getNonExistingColumns() ) ) {
                $this->io->newLine();
                $this->io->writeln(
                    $removeNonExistingColumns ?
                        '❕ The following non property represented columns were found and removed from the table.' :
                        '❕ The following columns are found in the table, but not represented as properties. Remove them or add them as a property to the entity. You can easily remove them by adding the --remove_non_existing=1 flag to this command'
                );
                $this->io->listing( array_map( static fn( Field $field ): string => $field->getDatabaseName(), $result->getNonExistingColumns() ) );

            }

            if ( ! empty( $result->getNonExistingIndexes() ) ) {
                $this->io->newLine();
                $this->io->writeln(
                    $removeNonExistingColumns ?
                        '❕ The following non property represented indexes were found and removed from the table.' :
                        '❕ The following indexes are found in the table, but not represented as properties. Remove them or add them as a property to the entity. You can easily remove them by adding the --remove_non_existing=1 flag to this command'
                );
                $this->io->listing( array_map( static fn( Index $index ): string => $index->getName(), $result->getNonExistingIndexes() ) );
            }

		} catch (\Exception $exception) {
			$this->io->error($exception->getMessage());
		}

		return 0;
	}

    /**
     * Autowire entities to class
     *
     * @param iterable $entities
     */
    #[Autowire]
    public function setEntities( #[Autowire(tag: KernelDiTags::ENTITY)] iterable $entities ): void {
        foreach ($entities as $entity) {
            $this->entities[$entity::class] = $entity;
        }
    }

    #[Autowire]
    public function setTableFactory( TableFactory $tableFactory ): void {
        $this->tableFactory = $tableFactory;
    }

}