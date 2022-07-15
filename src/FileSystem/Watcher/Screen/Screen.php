<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem\Watcher\Screen;


use AlecRabbit\Snake\Contracts\SpinnerInterface;
use React\EventLoop\LoopInterface;
use Swift\Console\Style\ConsoleStyle;
use Swift\DependencyInjection\Attributes\DI;
use Swift\FileSystem\Watcher\Config\WatchList;
use Swift\FileSystem\Watcher\ResourceWatcherBased\ResourceWatcherResult;

#[DI( autowire: false )]
final class Screen {
    
    public function __construct(
        private readonly ConsoleStyle $output,
        private readonly SpinnerInterface $spinner,
    ) {
    }
    
    public function showOptions( WatchList $watchList ): void {
        $this->output->title('File watcher');
        $this->output->writeln('<fg=blue>  Server will restart on changes in configured files and/or directories</>');
        $this->output->newLine();
        
        $this->showWatchList( $watchList );
        
        $this->output->newLine(3);
    }
    
    private function showWatchList( WatchList $watchList ): void {
        $watching = $watchList->isWatchingForEverything() ? '*.*' : implode( ', ', $watchList->paths() );
        
        $this->output->table(
            [
                'Watching',
                'Ignoring',
            ],
            [
                [
                    $watching,
                    $watchList->hasIgnoring() ? implode( ', ', $watchList->ignore() ) : 'Nothing to ignore',
                ],
            ],
        );
    }
    
    private function comment( string $text ): void {
        $text = sprintf( '<comment>%s</comment>', $this->message( $text ) );
        $this->output->writeln( $text );
    }
    
    private function info( string $text ): void {
        $text = sprintf( '<info>%s</info>', $this->message( $text ) );
        $this->output->writeln( $text );
    }
    
    private function warning( string $text ): void {
        $text = sprintf( '<fg=red>%s</>', $this->message( $text ) );
        $this->output->writeln( $text );
    }
    
    public function start( string $command ): void {
        $command = str_replace( [ 'exec', PHP_BINARY ], [ '', 'php' ], $command );
        $this->info( sprintf( 'starting `%s`', trim( $command ) ) );
    }
    
    public function restarting( ResourceWatcherResult $changes ): void {
        $this->spinner->erase();
        $this->output->writeln( '' );
        
        $this->comment('Changes were detected');
        $this->output->listing(
            [
                ...array_map( static function( string $file ): string {
                    return sprintf('<fg=bright-green>[new] %s</>', $file);
                }, $changes->getNewFiles()),
                ...array_map( static function( string $file ): string {
                    return sprintf('<fg=green>[updated] %s</>', $file);
                }, $changes->getUpdatedFiles()),
                ...array_map( static function( string $file ): string {
                    return sprintf('<fg=red>[deleted] %s</>', $file);
                }, $changes->getDeletedFiles()),
            ],
        );
        $this->info( 'restarting due to changes...' );
        
    }
    
    public function showSpinner( LoopInterface $loop ): void {
        $this->spinner->begin();
        $loop->addPeriodicTimer( $this->spinner->interval(), function () {
            $this->spinner->spin();
        } );
    }
    
    private function message( string $text ): string {
        return sprintf( '[FILE WATCHER] %s', $text );
    }
    
}