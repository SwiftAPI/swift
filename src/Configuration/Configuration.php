<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration;

use Swift\Configuration\Cache\ConfigurationCache;
use Swift\Configuration\Exception\UnknownConfigurationKeyException;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\DependencyInjection\Attributes\DI;
use Swift\DependencyInjection\ServiceLocatorInterface;

/**
 * Class Configuration
 * @package Swift\Configuration
 */
#[Autowire]
#[DI( aliases: [ ConfigurationInterface::class . ' $configuration' ] )]
class Configuration implements ConfigurationInterface {
    
    /** @var ConfigurationScope[] $configs */
    private array $configs = [];
    
    private ?CachedConfiguration $cachedConfiguration = null;
    
    public function __construct(
        protected readonly ServiceLocatorInterface $serviceLocator,
        protected readonly ConfigurationCache      $configurationCache,
    ) {
        $cache = $this->configurationCache->getItem( ConfigurationCache::serializeClassName( static::class ) );
        if ( ! $cache->isHit() ) {
            $this->setConfiguration( $this->serviceLocator->getServiceInstancesByTag( DiTags::CONFIGURATION ) );
            
            return;
        }
        
        /** @var \Swift\Configuration\CachedConfiguration $cachedConfiguration */
        $cachedConfiguration = $cache->get();
        
        if ( Utils::isDevModeOrDebug( $cachedConfiguration ) ) {
            $this->setConfiguration( $this->serviceLocator->getServiceInstancesByTag( DiTags::CONFIGURATION ) );
            
            return;
        }
        
        $this->cachedConfiguration = $cachedConfiguration;
    }
    
    
    /**
     * @param ConfigurationScope[] $configs
     */
    public function setConfiguration( array $configs ): void {
        foreach ( $configs as /** @var ConfigurationScope */ $config ) {
            $scope = $config->getScope();
            if ( is_array( $scope ) ) {
                foreach ( $scope as $item ) {
                    if ( array_key_exists( $item, $this->configs ) ) {
                        throw new \InvalidArgumentException( 'Duplicate configuration scope is not possible' );
                    }
                    $this->configs[ $item ] = $config;
                }
            } else {
                if ( array_key_exists( $scope, $this->configs ) ) {
                    throw new \InvalidArgumentException( 'Duplicate configuration scope is not possible' );
                }
                $this->configs[ $scope ] = $config;
            }
        }
    }
    
    public function getCacheInstance(): CachedConfiguration {
        return $this->cachedConfiguration ?: new CachedConfiguration( $this->configs );
    }
    
    /**
     * @inheritDoc
     */
    public function get( string $identifier, string $scope ): mixed {
        if ( $this->cachedConfiguration ) {
            return $this->cachedConfiguration->get( $identifier, $scope );
        }
        
        if ( ! $this->has( $identifier, $scope ) ) {
            throw new UnknownConfigurationKeyException( sprintf( 'Could not find registered configuration for scope %s', $scope ) );
        }
        
        return $this->configs[ $scope ]->get( $identifier, $scope );
    }
    
    /**
     * @inheritDoc
     */
    public function set( mixed $value, string $identifier, string $scope ): void {
        if ( $this->cachedConfiguration ) {
            $this->cachedConfiguration->set( $value, $identifier, $scope );
            
            return;
        }
        
        if ( ! $this->has( $identifier, $scope ) ) {
            throw new UnknownConfigurationKeyException( sprintf( 'Could not find registered configuration for scope %s', $scope ) );
        }
        
        $this->configs[ $scope ]->set( $value, $identifier, $scope );
    }
    
    /**
     * @inheritDoc
     */
    public function has( string $identifier, ?string $scope = null ): bool {
        if ( $this->cachedConfiguration ) {
            return $this->cachedConfiguration->has( $identifier, $scope );
        }
        
        if ( ! array_key_exists( $scope, $this->configs ) ) {
            return false;
        }
        
        return $this->configs[ $scope ]->has( $identifier, $scope );
    }
    
    public function persist(): void {
        if ( $this->cachedConfiguration ) {
            $this->cachedConfiguration->persist();
            
            return;
        }
        
        foreach ( $this->configs as $config ) {
            $config->persist();
        }
    }
    
}