<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Console;

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\ServiceLocatorInterface;
use Swift\ORM\EntityManager;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\HelperSet;

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
        private EntityManager $entityManager,
    ) {
        parent::__construct('<fg=green;options=bold>SWIFT CONSOLE 🚀</>');
    }

    /**
     * @param InputInterface|null $input
     * @param OutputInterface|null $output
     *
     * @throws \Exception
     */
    public function run(InputInterface $input = null, OutputInterface $output = null): void {
        $this->registerCommands();

        parent::run();
    }

    public function getDefaultInputDefinition(): InputDefinition {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(new InputOption('--track-timing', '-t', InputOption::VALUE_NONE, 'Track and report execution time of command'));

        return $definition;
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

        ConsoleRunner::addCommands($this);
    }

    public function getHelp(): string {
        return PHP_EOL . $this->getLogo() . PHP_EOL . PHP_EOL . parent::getHelp();
    }

    private function getLogo(): string {
        return "<fg=green>
 ________  ___       __   ___  ________ _________   
|\   ____\|\  \     |\  \|\  \|\  _____\\___   ___\ 
\ \  \___|\ \  \    \ \  \ \  \ \  \__/\|___ \  \_| 
 \ \_____  \ \  \  __\ \  \ \  \ \   __\    \ \  \  
  \|____|\  \ \  \|\__\_\  \ \  \ \  \_|     \ \  \ 
    ____\_\  \ \____________\ \__\ \__\       \ \__\
   |\_________\|____________|\|__|\|__|        \|__|
   \|_________|                                     
                                                                  
                </>";
    }

}