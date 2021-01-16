<?php declare(strict_types=1);

namespace Swift\Console\Command;

use Swift\Authentication\Model\Client;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class GetClientCommand extends Command {

	/**
	 * @var Client $modelClient
	 */
	private $modelClient;

	/**
	 * the name of the command (the part after "bin/henri")
	 * @var string $defaultName
	 */
	protected static $defaultName = 'client:get';


	/**
	 * GetClientCommand constructor.
	 *
	 * @param Client $modelClient
	 */
	public function __construct(
		Client  $modelClient
	)
	{
		$this->modelClient    = $modelClient;

		parent::__construct();
	}

	/**
	 * Method to set command configuration
	 */
	protected function configure() {
		$this
			// the short description shown while running "php bin/console list"
			->setDescription('Get a client')

			// the full command description shown when running the command with
			// the "--help" option
			->setHelp('This command will show you a client by id, apikey or domain.')

			// configure an argument
			->addArgument('search', InputArgument::REQUIRED, 'Find client by id:/apikey:/domain:')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$search = $input->getArgument('search');

		if (!$search) {
			$output->writeln('Specify a search');
			return 0;
		}

		$searchArr = explode(':', $search);
		if (count($searchArr) < 2) {
			$output->writeln('Invalid argument. Search with either id/apikey/domain + : + value');
			return 0;
		}

		$searchKey  = $searchArr[0];
		if ($searchKey !== 'id' && $searchKey !== 'apikey' && $searchKey !== 'domain') {
			$output->writeln('Invalid argument. Search with either id/apikey/domain + : + value');
			return 0;
		}

		$searchValue    = str_replace($searchKey . ':', '', $search);
		try {
			$client = $this->modelClient->getClient($searchKey, $searchValue);

			if (is_null($client)) {
				throw new \Exception('No client found by ' . $searchKey . ': ' . $searchValue);
			} elseif (!is_null($client)) {
				foreach (get_object_vars($client) as $key => $value) {
					if (!isset($client->{$key})) {
						throw new \Exception('No client found by ' . $searchKey . ': ' . $searchValue);
					}
				}
			}
		} catch (\Exception $exception) {
			$output->writeln($exception->getMessage());
			return 0;
		}

		$output->writeln('Client found');
		foreach (get_object_vars($client) as $key => $value) {
			$output->writeln($key . ' => ' . $value);
		}

		return 0;
	}

}