<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration\Events;


use JetBrains\PhpStorm\ArrayShape;
use Swift\Configuration\Cache\ConfigurationCache;
use Swift\Configuration\Configuration;
use Swift\Configuration\ConfigurationInterface;
use Swift\Configuration\Utils;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Kernel\Event\KernelOnBeforeShutdown;

#[Autowire]
final class OnKernelShutdownSubscriber implements \Swift\Events\EventSubscriberInterface {
    
    public function __construct(
        private readonly ConfigurationInterface $configuration,
        private readonly ConfigurationCache     $configurationCache,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    #[ArrayShape( [ KernelOnBeforeShutdown::class => "\string[][]" ] )]
    public static function getSubscribedEvents(): array {
        return [
            KernelOnBeforeShutdown::class => [['persistCache'], ['createClassCache']],
        ];
    }
    
    public function persistCache(): void {
        $this->configuration->persist();
    }
    
    public function createClassCache( KernelOnBeforeShutdown $event ): void {
        // No cache if dev mode or debug mode
        if (Utils::isDevModeOrDebug( $this->configuration )) {
            return;
        }
    
        $cacheItem = $this->configurationCache->getItem( ConfigurationCache::serializeClassName( Configuration::class ) );
        $cacheItem->set( $this->configuration->getCacheInstance() );
        $this->configurationCache->save( $cacheItem );
    }
    
    
}