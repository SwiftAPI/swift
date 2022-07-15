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
use Swift\Console\Command\AbstractCommand;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Kernel\KernelDiTags;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCachesCommand extends AbstractCommand {
    
    /** @var \Swift\Cache\AbstractCache[] $caches */
    private array $caches;
    private ConfigurationInterface $configuration;
    
    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        return 'cache:list';
    }
    
    public function configure(): void {
        $this
            ->setDescription( 'List all caches' );
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
    
    protected function execute( InputInterface $input, OutputInterface $output ): int {
        $this->io->newLine();
        $this->io->writeln( sprintf( '<fg=yellow>  Found %s caches</>', count( $this->caches ) ) );
        $this->io->text(
            sprintf(
                ' <fg=cyan>Caching is currently %senabled</>',
                ($this->configuration->get('app.mode', 'root') === 'develop') || $this->configuration->get('app.debug', 'root') ? 'not ' : '',
            )
        );
        
        $formatted = [];
        foreach ( $this->caches as $cache ) {
            $formatted[] = [
                $cache->getFullName(),
                $cache::class,
            ];
        }
    
        ksort($formatted);
        
        $this->io->table(['name', 'class'], $formatted);
        
        return AbstractCommand::SUCCESS;
    }
    
}