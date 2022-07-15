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
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Kernel\KernelDiTags;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetModeCommand extends \Swift\Console\Command\AbstractCommand {
    
    /** @var \Swift\Cache\AbstractCache[] $caches */
    private array $caches;
    
    public function __construct(
        private ConfigurationInterface $configuration,
    ) {
        parent::__construct();
    }
    
    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        return 'app:mode:set';
    }
    
    protected function configure(): void {
        $this
            ->setDescription('Set current application mode')
            
            ->addArgument( 'mode', InputArgument::REQUIRED, 'Mode to set, this is either develop or production' )
        ;
    }
    
    protected function execute( InputInterface $input, OutputInterface $output ): int {
        $newMode = $input->getArgument( 'mode' );
        
        if (!in_array( $newMode, ['develop', 'production'])) {
            $this->io->error( sprintf('Could not deploy app to "%s" mode. Expected "develop" or "production".', $newMode) );
            
            return AbstractCommand::SUCCESS;
        }
        
        if ($newMode === $this->configuration->get( 'app.mode', 'root' )) {
            $this->io->note(sprintf('App is already running in "%s" mode. Therefore nothing has changed', $newMode));
            
            return AbstractCommand::SUCCESS;
        }
        
        $this->configuration->set( $newMode, 'app.mode', 'app' );
        
        
        $this->io->newLine(1);
        $this->io->writeln( sprintf('Changed application mode to: %s', $newMode) );
        $this->io->newLine(1);
        
        if ($newMode === 'production') {
            $this->configuration->set(false, 'app.debug', 'app');
            $this->io->note( 'Debugging is not a real feature in production environments and most definitely not recommended. Therefore we disabled debugging.' );
        }
        
        foreach ($this->caches as $cache) {
            $cache->clear();
        }
        
        $this->io->note('Do not forget to clean the clean the cache after changed the application mode. Run cache:flush');
        $this->io->newLine(1);
        
        return AbstractCommand::SUCCESS;
    }
    
    #[Autowire]
    public function setCaches( #[Autowire( tag: KernelDiTags::CACHE_TYPE )] ?iterable $caches ): void {
        if ( ! $caches ) {
            return;
        }
        
        $caches = iterator_to_array( $caches );
        
        foreach ( $caches as $cache ) {
            $this->caches[ $cache::class ] = $cache;
        }
    }
    
    
}