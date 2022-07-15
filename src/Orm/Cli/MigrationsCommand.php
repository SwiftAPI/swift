<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Cli;


use Swift\Console\Command\AbstractCommand;
use Swift\DependencyInjection\Attributes\DI;
use Swift\Orm\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[DI( exclude: true )]
class MigrationsCommand extends AbstractCommand {
    
    public function __construct(
        private readonly Factory $ormFactory,
    ) {
        parent::__construct();
    }
    
    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        return 'orm:migrate';
    }
    
    public function run( InputInterface $input, OutputInterface $output ): int {
        var_dump( $this->ormFactory->getSchemaFactory()->getMigrator()->getMigrations() );
        
        return AbstractCommand::SUCCESS;
    }
    
}