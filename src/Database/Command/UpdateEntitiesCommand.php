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
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\DiTags;
use Swift\Model\Entity;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class UpdateEntitiesCommand
 * @package Swift\Database\Command
 */
#[Autowire]
class UpdateEntitiesCommand extends AbstractCommand {

    /** @var Entity[] */
    private array $entities;


    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        return 'database:entities:update';
    }

    /**
	 * Method to set command configuration
	 */
	protected function configure() {
		$this
			// the short description shown while running "php bin/console list"
			->setDescription('Update all tables by their entities')

			// the full command description shown when running the command with
			// the "--help" option
			->setHelp('This command will update tables from their respective entity')

			// configure an argument
			->addArgument('remove_non_existing_columns', InputArgument::OPTIONAL, 'Remove database columns which are not represented in the given entity. Do this by adding the flag remove_non_existing to this command.')

			->addArgument('drop_table_if_exists', InputArgument::OPTIONAL, 'Drop the tables if they exist. Do this by adding the flag drop_table to this command.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$removeNonExistingColumns   = $input->getArgument('remove_non_existing_columns') ?? '';
		$removeNonExistingColumns   = strtolower($removeNonExistingColumns) === 'remove_non_existing';
		$dropTableIfExists          = !is_null($input->getArgument('drop_table_if_exists')) && strtolower($input->getArgument('drop_table_if_exists')) === 'drop_table';

		foreach ($this->entities as $entity) {
			try {
				$this->updateEntity($entity, $removeNonExistingColumns, $dropTableIfExists);
				$this->io->newLine(1);
			} catch (\Exception $exception) {
				$output->writeln($exception->getMessage());
			}
		}

		return 0;
	}

	private function updateEntity(Entity $entity, bool $removeNonExistingColumns, bool $dropTableIfExists) {
	    $io = $this->io;
	    $section = $this->createOutputSection();
		try {
			$section->writeln('⏳ <fg=blue;options=bold>Updating:</> "' . $entity->getTableName() . '" for entity "' . $entity::class . '"');
			$nonExistingColumns = $entity->updateTable($removeNonExistingColumns, $dropTableIfExists);
			$section->clear(1);
            $section->writeln('✅ <fg=green;options=bold>Success:</> Updated "' . $entity->getTableName() . '" for entity "' . $entity::class . '"');

			if (!empty($nonExistingColumns) && !$removeNonExistingColumns) {
                $this->io->newLine();
				$io->writeln('❕ The following columns are found in the table, but not represented as properties. Remove them or add them as a property to the entity. You can easily remove them by adding the remove_non_existing flag to this command');
				$io->listing($nonExistingColumns);
			} elseif (!empty($nonExistingColumns) && $removeNonExistingColumns) {
                $this->io->newLine();
				$io->writeln('❕ The following non property represented columns were found and removed from the table.');
                $io->listing($nonExistingColumns);
			}

		} catch (\Exception $exception) {
		    $section->clear(1);
            $section->writeln('❌ <fg=red;options=bold>Failed:</> Has not updated "' . $entity->getTableName() . '" for entity "' . $entity::class . '. See: <fg=blue>https://henrivantsant.github.io/swift-docs/docs/database</>"');
			$io->error($exception->getMessage());
		}
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