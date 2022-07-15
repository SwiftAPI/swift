<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem\Watcher;


use AlecRabbit\Snake\Contracts\SpinnerInterface;
use React\EventLoop\LoopInterface;
use Swift\Configuration\ConfigurationInterface;
use Swift\Configuration\Utils;
use Swift\Console\Style\ConsoleStyle;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\FileSystem\Watcher\Config\Config;
use Swift\FileSystem\Watcher\Config\InputExtractor;
use Swift\FileSystem\Watcher\ResourceWatcherBased\ChangesListener;
use Swift\FileSystem\Watcher\Screen\Screen;
use Swift\FileSystem\Watcher\Screen\SpinnerFactory;
use Swift\Runtime\Cli\AbstractRuntimeCommand;
use Swift\Runtime\RuntimeInterface;
use Swift\Runtime\RuntimeKernelInterface;

#[Autowire]
final class Kernel implements RuntimeKernelInterface {
    
    public function __construct(
        private readonly ConfigurationInterface $configuration,
        private readonly RuntimeInterface       $runtime,
    ) {
    }
    
    public function run( ConsoleStyle $io, AbstractRuntimeCommand $command, LoopInterface $loop ): int {
        if ( ! Utils::isDevMode( $this->configuration ) || ! $this->configuration->get( 'file_watcher.enabled', 'runtime' ) ) {
            return 0;
        }
        
        $config  = $this->buildConfig();
        $spinner = SpinnerFactory::create( $io->getOutput(), $config->spinnerDisabled() );
        
        $this->addTerminationListeners( $loop, $spinner );
        
        $screen     = new Screen( $io, $spinner );
        $filesystem = new ChangesListener( $loop );
        
        $screen->showOptions( $config->watchList() );
        $processRunner = new ProcessRunner( $loop, $screen, $this->runtime );
        
        $watcher = new Watcher( $filesystem );
        $watcher->startWatching(
            $processRunner,
            $config->watchList(),
            $config->delay()
        );
        
        
        return 0;
    }
    
    /**
     * When terminating the watcher we need to manually restore the cursor after the spinner.
     */
    private function addTerminationListeners( LoopInterface $loop, SpinnerInterface $spinner ): void {
        register_shutdown_function( static function () use ( $spinner ): void {
            $spinner->end();
        } );
    }
    
    private function buildConfig(): Config {
        return Config::fromArray( $this->configuration->get( 'file_watcher', 'runtime' ) );
    }
    
    public function isDebug(): bool {
        return Utils::isDebug( $this->configuration );
    }
    
    public function finalize(): void {
    
    }
    
}