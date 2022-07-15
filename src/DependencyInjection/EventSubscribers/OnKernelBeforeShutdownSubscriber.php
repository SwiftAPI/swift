<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\DependencyInjection\EventSubscribers;


use Swift\Configuration\ConfigurationInterface;
use Swift\Configuration\Utils;
use Swift\Events\Attribute\ListenTo;
use Swift\Events\EventListenerInterface;
use Swift\FileSystem\FileSystem;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\DependencyInjection\Container;
use Swift\DependencyInjection\ContainerDumper;
use Swift\Kernel\Event\KernelOnBeforeShutdown;

#[Autowire]
class OnKernelBeforeShutdownSubscriber implements EventListenerInterface {
    
    public function __construct(
        private readonly ConfigurationInterface $configuration,
        private readonly FileSystem $fileSystem,
        private readonly ContainerDumper $containerDumper,
    ) {
    }
    
    #[ListenTo( event: KernelOnBeforeShutdown::class )]
    public function onKernelBeforeShutdown(): void {
        // No cache if dev mode or debug mode
        if ( Utils::isDevModeOrDebug( $this->configuration ) ) {
            return;
        }
        
        global $container;
        
        if ( ! is_a( $container, Container::class, true ) ) {
            return;
        }
        
        if ( ! $this->fileSystem->dirExists( '/var/cache/di' ) ) {
            $this->fileSystem->createDirectory( '/var/cache/di' );
        }
        
        if ( ! $this->fileSystem->exists( '/var/cache/di/container.php' ) ) {
            $this->containerDumper->dump( '/var/cache/di/container.php', $container );
        }
    }
}