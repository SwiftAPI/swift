<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router\Cli;

use Swift\Console\Command\AbstractCommand;
use Swift\Router\Router;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListRoutesCommand
 * @package Swift\Router\Command
 */
class ListRoutesCommand extends AbstractCommand {

    /**
     * ListRoutesCommand constructor.
     */
    public function __construct(
        private Router $router,
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        return 'routing:list';
    }

    protected function configure(): void {
        $this
            ->setDescription('List all or any available route(s)')
            ->setHelp('List all available routes (or filter by tag or name)')
            ->addOption('--tag', null, InputOption::VALUE_OPTIONAL)
            ->addOption('--name', null, InputOption::VALUE_OPTIONAL)
        ;
    }

    protected function execute( InputInterface $input, OutputInterface $output ): int {
        return !empty($input->getOption('name')) ? $this->getByName($input->getOption('name')) : $this->getList();
    }

    /**
     * Get list of routes
     *
     * @return int
     */
    private function getList(): int {
        $definedRoutes = !empty($this->input->getOption('tag')) ? $this->router->getTaggedRoutes($this->input->getOption('tag')) : $this->router->getRoutes();
        $routes = [];

        $this->io->newLine();
        $this->io->writeln(sprintf('<fg=yellow>  Found %s routes</>', $definedRoutes->count()));
        $this->io->text(' <fg=cyan>Search by name for more details</>');

        foreach ($definedRoutes as $i => $route) {
            $param = $route->getName() ?? 'unnamed' . $i;
            $routes[$param] = array(
                $route->getName(),
                implode(', ', $route->getMethods()),
                $route->getController() . '::' . $route->getAction() . '()',
                $route->getFullPath(),
                implode(', ', $route->getTags()->getIterator()->getArrayCopy()),
            );
        }

        ksort($routes);

        $this->io->table(array('name', 'methods', 'controller', 'full_path', 'tags'), $routes);

        return AbstractCommand::SUCCESS;
    }

    /**
     * Get specific route by name
     *
     * @param string $name
     *
     * @return int
     */
    private function getByName(string $name): int {
        $this->io->newLine();
        if (!$route = $this->router->getRoute($name)) {
            $this->io->writeln(sprintf('<fg=yellow>[404] Route with name "%s" not found</>', $name));
            $this->io->newLine();
            return AbstractCommand::SUCCESS;
        }

        $this->io->writeln('<fg=yellow>  Route details</>');
        $this->io->horizontalTable(array('name', 'methods', 'controller', 'full_path', 'tags', 'isGranted', 'params', 'full_regex'), array(array(
            $route->getName(),
            implode(', ', $route->getMethods()),
            $route->getController() . '::' . $route->getAction() . '()',
            $route->getFullPath(),
            implode(', ', $route->getTags()->getIterator()->getArrayCopy()),
            implode(', ', $route->getIsGranted()),
            implode(', ', $route->getParams()->getIterator()->getArrayCopy()),
            $route->getFullRegex(),
        )));

        return AbstractCommand::SUCCESS;
    }

}