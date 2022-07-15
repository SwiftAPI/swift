<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Cli;


use Swift\Configuration\ConfigurationInterface;
use Swift\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowModeCommand extends \Swift\Console\Command\AbstractCommand {
    
    public function __construct(
        private ConfigurationInterface $configuration,
    ) {
        parent::__construct();
    }
    
    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        return 'app:mode:show';
    }
    
    protected function configure(): void {
        $this
            ->setDescription('Show current application mode')
        ;
    }
    
    protected function execute( InputInterface $input, OutputInterface $output ): int {
        $this->io->newLine(1);
        $this->io->writeln( sprintf('Current application mode is: %s', $this->configuration->get( 'app.mode', 'root' )) );
        $this->io->newLine(1);
        
        return AbstractCommand::SUCCESS;
    }
    
    
}