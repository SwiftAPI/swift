<?php declare(strict_types=1);

namespace Swift\Database\Command;

use Swift\Console\Command\Command;
use Swift\Kernel\ContainerService\ContainerService;
use Swift\Model\Entity;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class UpdateEntityCommand extends Command {

	/**
	 * the name of the command (the part after "bin/henri")
	 * @var string $defaultName
	 */
	protected static $defaultName = 'database:entity:update';

    /**
     * GetClientCommand constructor.
     *
     * @param ContainerService|null $container
     */
	public function __construct(
	    private ?ContainerService $container = null,
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
			$output->writeln('No entity given');
			return 0;
		}

		if (!$this->container->has($entityName)) {
			$output->writeln('Entity ' . $entityName . ' is not found. Is it registered in the Container?');
			return 0;
		}

		$entity = $this->container->get($entityName);
		if (!is_subclass_of($entity, Entity::class)) {
			$output->writeln($entityName . ' is not a valid entity');
			return 0;
		}

		try {
			$output->writeln('Updating table ' . $entity->getTableName() . ' for entity ' . $entityName);
			$nonExistingColumns = $entity->updateTable($removeNonExistingColumns, false);
			$output->writeln('Updated ' . $entity->getTableName() . ' successfully');

			if (!empty($nonExistingColumns) && !$removeNonExistingColumns) {
				$nonExistingColumns = implode(', ', $nonExistingColumns);
				$output->writeln('The following columns are found in the table, but not represented as properties: ' . $nonExistingColumns . '. Remove them or add them as a property to the entity. You can easily remove them by adding the remove_non_existing flag to this command');
			} elseif (!empty($nonExistingColumns) && $removeNonExistingColumns) {
				$nonExistingColumns = implode(', ', $nonExistingColumns);
				$output->writeln('The following non property represented columns were found and removed from the table: ' . $nonExistingColumns);
			}

		} catch (\Exception $exception) {
			$output->writeln($exception->getMessage());
		}

		return 0;
	}

}