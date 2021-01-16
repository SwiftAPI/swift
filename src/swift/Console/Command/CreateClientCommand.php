<?php declare(strict_types=1);

namespace Swift\Console\Command;

use Swift\Authentication\Model\Client;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class CreateClientCommand extends Command {

	/**
	 * @var Client $modelClient
	 */
	private $modelClient;

	/**
	 * the name of the command (the part after "bin/henri")
	 * @var string $defaultName
	 */
	protected static $defaultName = 'client:create';


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
			->setDescription('Create a client')

			// the full command description shown when running the command with
			// the "--help" option
			->setHelp('This command will show you a client by id, apikey or domain.')

			// configure an argument
			->addArgument('domain', InputArgument::REQUIRED, 'client domain without http(s) or www prefix')
			->addArgument('secret', InputArgument::OPTIONAL, 'Client secret (when empty, secret is automatically generated)');
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$domain = $input->getArgument('domain');
		$secret = $input->getArgument('secret');

		try {
			$newClient  = $this->modelClient->createClient($domain, '', $secret);
		} catch (\Exception $exception) {
			$output->writeln($exception->getMessage());
			return 0;
		}

		$output->writeln('New client created');
		foreach (get_object_vars($newClient) as $key => $value) {
			$output->writeln($key . ': ' . $value);
		}

		return 0;
	}

}