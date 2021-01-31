<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Console;

use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\ServiceLocatorInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Application
 * @package Swift\Console
 */
#[Autowire]
final class Application extends \Symfony\Component\Console\Application {


    /**
     * Application constructor.
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(
        private ServiceLocatorInterface $serviceLocator,
    ) {
        parent::__construct();
    }

    public function run(InputInterface $input = null, OutputInterface $output = null): void {
        $this->registerCommands();

        parent::run();
    }

    /**
     * Method to register commands
     *
     * @throws \Exception
     */
    private function registerCommands() : void {
        $commands       = array();
        $commandClasses = $this->serviceLocator->getServicesByTag('kernel.command');

        foreach ($commandClasses as $commandClass) {
            $commands[] = $this->serviceLocator->get( $commandClass );
        }

        if (!empty($commands)) {
            $this->addCommands($commands);
        }
    }

}