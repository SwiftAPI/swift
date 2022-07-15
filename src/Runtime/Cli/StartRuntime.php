<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Runtime\Cli;


use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Runtime\Kernel;
use Swift\Runtime\KernelInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class StartRuntime extends AbstractRuntimeCommand {
    
    private KernelInterface $kernel;
    private ConsoleLogger $consoleLogger;
    
    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        return 'runtime:start';
    }
    
    protected function configure(): void {
        $this
            ->setDescription('Start runtime server')
            ->setHelp('Start runtime server')
        ;
    }
    
    protected function execute( InputInterface $input, OutputInterface $output ): int {
        $this->consoleLogger->setIo( $this->io );
        $this->consoleLogger->setCommand( $this );
        
        return $this->kernel->run( $this->io, $this );
    }
    
    /**
     * @param \Swift\Runtime\Kernel $kernel
     */
    #[Autowire]
    public function setKernel( Kernel $kernel ): void {
        $this->kernel = $kernel;
    }
    
    #[Autowire]
    public function setConsoleLogger( ConsoleLogger $consoleLogger ): void {
        $this->consoleLogger = $consoleLogger;
    }
    
}