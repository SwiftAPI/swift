<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router\Cli;

use Swift\Console\Command\AbstractCommand;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Router\RouterInterface;
use Swift\Router\Types\RouteMethod;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


#[Autowire]
class GenerateRouteCommand extends AbstractCommand {
    
    public function __construct(
        protected readonly RouterInterface $router,
    ) {
        parent::__construct();
    }
    
    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        return 'routing:generate';
    }
    
    /**
     * Configure command
     */
    protected function configure(): void {
        $this
            ->setDescription( 'Generate a url for a route by name' )
            ->setHelp( 'Generate a url for a route by name, and optional route parameters' )
            ->addOption( '--route', null, InputOption::VALUE_REQUIRED, 'Routename to reverse' )
            ->addOption( '--params', null, InputOption::VALUE_OPTIONAL, 'Parameters to pass to route, parameters should be comma separated and the name and value should be separated by :. e.g. book_id:3,category_slug:thriller', '' );
    }
    
    /**
     * @param InputInterface  $input  Input for command
     * @param OutputInterface $output Output helper for command
     *
     * @return int
     */
    protected function execute( InputInterface $input, OutputInterface $output ): int {
        $this->io->title( 'Generated url for ' . $input->getOption( 'route' ) ?? '' );
        
        $params        = [];
        $displayValues = [];
        foreach ( explode( ',', $input->getOption( 'params' ) ) as $param ) {
            if ( ! str_contains( $param, ':' ) ) {
                continue;
            }
            [ $name, $value ] = explode( ':', $param );
            $params[ $name ] = $value;
            $displayValues[] = [ $name, $value ];
        }
        
        if ( ! empty( $displayValues ) ) {
            $this->io->table( [ 'parameter', 'value' ], $displayValues );
        }
        
        $generatedRoute = $this->router->generate( $input->getOption( 'route' ), $params );
        
        $methods = array_map( static function ( RouteMethod $method ) {
            return $method->value;
        }, $generatedRoute->getRoute()->getMethods() );
        
        $this->io->writeln( sprintf('<info>Generated: </info>[%s] %s', implode(',', $methods), $generatedRoute->getPath()) );
        
        
        return AbstractCommand::SUCCESS; // OR AbstractCommand::FAILURE
    }
    
}