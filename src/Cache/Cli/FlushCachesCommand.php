<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Cache\Cli;


use Swift\Configuration\ConfigurationInterface;
use Swift\Configuration\Utils;
use Swift\Console\Command\AbstractCommand;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Kernel\KernelDiTags;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FlushCachesCommand extends AbstractCommand {
    
    /** @var \Swift\Cache\AbstractCache[] $caches */
    private array $caches;
    private ConfigurationInterface $configuration;
    
    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        return 'cache:flush';
    }
    
    public function configure(): void {
        $this
            ->setDescription( 'Clear all caches' )
        ;
    }
    
    protected function execute( InputInterface $input, OutputInterface $output ): int {
        $this->io->newLine();
        $this->io->title('Clearing caches');
        $this->io->writeln( 'Clearing all caches could take a minute.' );
    
        if ( Utils::isProductionMode( $this->configuration ) ) {
            $this->io->writeln('Not that rebuilding caches in a production environment could take a while and result in a slow load the first (few) times as the cache rebuilds.');
        }
        
        if ( Utils::isDevModeOrDebug( $this->configuration ) ) {
            $this->io->note('Caching is currently not enabled');
        }
        
        $countFlushed = 0;
        foreach ( $this->caches as $cache ) {
            $section = $this->createOutputSection();
            $section->writeln( '⏳ <fg=blue;options=bold>Clearing:</> "' . $cache->getFullName() );
            
            if (!$cache->clear()) {
                $section->clear(1);
                $section->writeln( '❌ <fg=red;options=bold>Failed:</> Has not cleared successfully');
            }
            
            $section->clear(1);
            $section->writeln( '✅ <fg=green;options=bold>Cleared:</> ' . $cache->getFullName() );
            $countFlushed++;
        }
        
        $this->io->newLine();
        $this->io->success( sprintf( 'Flushed %s of %s caches', $countFlushed, count( $this->caches ) ) );
        
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
    
    #[Autowire]
    public function setConfiguration( ConfigurationInterface $configuration ): void {
        $this->configuration = $configuration;
    }
    
}