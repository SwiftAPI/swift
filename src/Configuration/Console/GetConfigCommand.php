<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration\Console;

use Swift\Configuration\Configuration;
use Swift\Configuration\ConfigurationInterface;
use Swift\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class GetConfigCommand
 * @package Swift\Configuration\Console
 */
final class GetConfigCommand extends AbstractCommand {
    
    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        return 'config:get';
    }
    
    /**
     * GetConfigCommand constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(
        private readonly ConfigurationInterface $configuration,
    ) {
        parent::__construct();
    }
    
    /**
     * Method to set command configuration
     */
    protected function configure(): void {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription( 'Get a setting value' )
            
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp( 'This command will get setting values.' )
            
            // configure an argument
            ->addArgument( 'name', InputArgument::REQUIRED, 'The name of the setting' )
            ->addArgument( 'scope', InputArgument::REQUIRED, 'The scope of the setting' );
    }
    
    protected function execute( InputInterface $input, OutputInterface $output ): int {
        $value = $this->configuration->get( $input->getArgument( 'name' ), $input->getArgument( 'scope' ) );
        if ( $value ) {
            $output->writeln( 'Current value for ' . $input->getArgument( 'name' ) . ': ' . $value );
        } else {
            $output->writeln( 'Setting ' . $input->getArgument( 'name' ) . ' is not found or empty' );
        }
        
        return 0;
    }
    
    
}