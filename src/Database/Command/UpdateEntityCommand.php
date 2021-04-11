<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Database\Command;

use Swift\Console\Command\AbstractCommand;
use Swift\Console\Command\Command;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\ContainerAwareTrait;
use Swift\Kernel\DiTags;
use Swift\Model\Entity;
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

    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        return 'database:entity:update';
    }


    /**
	 * Method to set command configuration
	 */
	protected function configure() {
		$this
			// the short description shown while running "php bin/console list"
			->setDescription('Update a table from an entity')

			// the full command description shown when running the command with
			// the "--help" option
			->setHelp('This command will update a table from a given entity')

			// configure an argument
			->addArgument('entity', InputArgument::REQUIRED, 'Entity fully qualified class name')
			->addArgument('remove_non_existing_columns', InputArgument::OPTIONAL, 'Remove database columns which are not represented in the given entity')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$entityName = $input->getArgument('entity');
		$removeNonExistingColumns   = $input->getArgument('remove_non_existing_columns') ?? '';
		$removeNonExistingColumns   = strtolower($removeNonExistingColumns) === 'remove_non_existing';

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
			$nonExistingColumns = $entity->updateTable($removeNonExistingColumns, false, $this->io);
			$this->io->writeln(': success');
			$this->io->newLine();

            if (!empty($nonExistingColumns) && !$removeNonExistingColumns) {
                $this->io->newLine();
                $this->io->writeln('The following columns are found in the table, but not represented as properties. Remove them or add them as a property to the entity. You can easily remove them by adding the remove_non_existing flag to this command');
                $this->io->listing($nonExistingColumns);
            } elseif (!empty($nonExistingColumns) && $removeNonExistingColumns) {
                $this->io->newLine();
                $this->io->writeln('The following non property represented columns were found and removed from the table.');
                $this->io->listing($nonExistingColumns);
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
    public function setEntities( #[Autowire(tag: DiTags::ENTITY)] iterable $entities ): void {
        foreach ($entities as $entity) {
            $this->entities[$entity::class] = $entity;
        }
    }

}