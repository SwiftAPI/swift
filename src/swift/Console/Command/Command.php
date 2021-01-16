<?php declare(strict_types=1);

namespace Swift\Console\Command;

use Symfony\Component\Console\Command\Command as CommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends CommandInterface {

	// the name of the command (the part after "bin/henri")
	protected static $defaultName = 'app:create-user';

	protected function configure()
	{
		// ...
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// ...

		return 0;
	}

}