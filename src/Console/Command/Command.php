<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Console\Command;

use JetBrains\PhpStorm\Deprecated;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Attributes\DI;
use Swift\Kernel\DiTags;
use Symfony\Component\Console\Command\Command as CommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Command
 * @package Swift\Console\Command
 */
#[DI(tags: [DiTags::COMMAND]), Autowire, Deprecated(reason: 'Deprecated in favor of of AbstractCommand', replacement: AbstractCommand::class)]
abstract class Command extends CommandInterface {

	// the name of the command (the part after "bin/console")
	protected static $defaultName = 'app:create-user';

	protected function configure() {
		// ...
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		// ...

		return 0;
	}

}