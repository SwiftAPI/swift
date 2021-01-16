<?php declare(strict_types=1);

namespace Swift\Database\Command;

use Swift\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use \Swift\ContainerService\ContainerService;


class CreateEntityCommand extends Command {

	/**
	 * @var ContainerService $containerService
	 */
	private $containerService;

	/**
	 * the name of the command (the part after "bin/henri")
	 * @var string $defaultName
	 */
	protected static $defaultName = 'database:entity:create';


	/**
	 * GetClientCommand constructor.
	 *
	 */
	public function __construct() {
		global $containerBuilder;
		$this->containerService = $containerBuilder;

		parent::__construct();
	}

	/**
	 * Method to set command configuration
	 */
	protected function configure() {
		$this
			// the short description shown while running "php bin/console list"
			->setDescription('Create a table from an entity')

			// the full command description shown when running the command with
			// the "--help" option
			->setHelp('This command will create a table from a given entity')

			// configure an argument
			->addArgument('entity', InputArgument::REQUIRED, 'client domain without http(s) or www prefix')
			->addArgument('drop_table_if_exists', InputArgument::OPTIONAL, 'Drop table if exists (true or false)');
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$entityName = $input->getArgument('entity');
		$dropTable  = $input->getArgument('drop_table_if_exists') === "true";

		if (!$entityName) {
			$output->writeln('No entity given');
			return 0;
		}

		if (!$this->containerService->has($entityName)) {
			$output->writeln('Entity ' . $entityName . ' is not found. Is it registered in the Container?');
			return 0;
		}

		$entity = $this->containerService->get($entityName);
		if (!is_subclass_of($entity, 'Swift\Model\Entity\Entity')) {
			$output->writeln($entityName . ' is not a valid entity');
			return 0;
		}

		try {
			$output->writeln('Creating table ' . $entity->getTableName() . ' for entity ' . $entityName);
			$entity->createTable($dropTable);
			$output->writeln('Created ' . $entity->getTableName() . ' successfully');
		} catch (\Exception $exception) {
			$output->writeln($exception->getMessage());
		}

		return 0;
	}

}