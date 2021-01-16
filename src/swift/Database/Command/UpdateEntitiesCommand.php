<?php declare(strict_types=1);

namespace Swift\Database\Command;

use Swift\Console\Command\Command;
use Swift\Model\Entity;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Swift\Kernel\ContainerService\ContainerService;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class UpdateEntitiesCommand
 * @package Swift\Database\Command
 */
class UpdateEntitiesCommand extends Command {

	/**
	 * the name of the command (the part after "bin/henri")
	 * @var string $defaultName
	 */
	protected static $defaultName = 'database:entities:update';


    /**
     * GetClientCommand constructor.
     *
     * @param ContainerService|null $container
     */
	public function __construct(
	    private ?ContainerService $container,
    ) {
		global $containerBuilder;
		$this->container = $containerBuilder;

		parent::__construct();
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
	    $io = new SymfonyStyle($input, $output);
		$removeNonExistingColumns   = $input->getArgument('remove_non_existing_columns') ?? '';
		$removeNonExistingColumns   = strtolower($removeNonExistingColumns) === 'remove_non_existing';
		$dropTableIfExists          = !is_null($input->getArgument('drop_table_if_exists')) && strtolower($input->getArgument('drop_table_if_exists')) === 'drop_table';

		$entities   = $this->container->getDefinitionsByTag('kernel.entity');

		foreach ($entities as $entity) {
			try {
				$this->updateEntity($io, $entity, $removeNonExistingColumns, $dropTableIfExists);
			} catch (\Exception $exception) {
				$output->writeln($exception->getMessage());
			}
		}

		return 0;
	}

	private function updateEntity(SymfonyStyle $io, string $entityName, bool $removeNonExistingColumns, bool $dropTableIfExists) {
		if (!$this->container->has($entityName)) {
			$io->writeln('Entity ' . $entityName . ' is not found. Is it registered in the Container?');
			return 0;
		}

		if (!is_subclass_of($entityName, Entity::class, true)) {
			$io->writeln($entityName . ' is not a valid entity');
			return 0;
		}

        $entity = $this->container->get($entityName);

		try {
			$io->writeln('Updating table ' . $entity->getTableName() . ' for entity ' . $entityName);
			$nonExistingColumns = $entity->updateTable($removeNonExistingColumns, $dropTableIfExists);
			$io->writeln('Updated ' . $entity->getTableName() . ' successfully');

			if (!empty($nonExistingColumns) && !$removeNonExistingColumns) {
				$nonExistingColumns = implode(', ', $nonExistingColumns);
				$io->writeln('The following columns are found in the table, but not represented as properties: ' . $nonExistingColumns . '. Remove them or add them as a property to the entity. You can easily remove them by adding the remove_non_existing flag to this command');
			} elseif (!empty($nonExistingColumns) && $removeNonExistingColumns) {
				$nonExistingColumns = implode(', ', $nonExistingColumns);
				$io->writeln('The following non property represented columns were found and removed from the table: ' . $nonExistingColumns);
			}

		} catch (\Exception $exception) {
			$io->error($exception->getMessage());
		}
	}
}