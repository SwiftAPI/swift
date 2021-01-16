<?php declare(strict_types=1);

namespace Swift\Console;

use Swift\Kernel\ContainerService\ContainerService;

class Console {

	/**
	 * @var Application $application
	 */
	private Application $application;

	/**
	 * @var ContainerService    $containerService
	 */
	private ContainerService $containerService;

	/**
	 * Console constructor.
	 *
	 * @param Application $application
	 */
	public function __construct(
		Application $application
	) {
		global $containerBuilder;

		$this->application      = $application;
		$this->containerService = $containerBuilder;
	}

	/**
	 * Method to run command line
	 *
	 * @throws \Exception
	 */
	public function run() : void {
		// Get all the commands
		$this->registerCommands();

		// Run application
		$this->application->run();
	}

	/**
	 * Method to register commands
	 *
	 * @throws \Exception
	 */
	private function registerCommands() : void {
		$commands       = array();
		$commandClasses = $this->containerService->getDefinitionsByTag('kernel.command');

		foreach ($commandClasses as $commandClass) {
			$commands[] = $this->containerService->get( $commandClass );
		}

		if (!empty($commands)) {
			$this->application->addCommands($commands);
		}
	}


}