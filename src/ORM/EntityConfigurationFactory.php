<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\ORM;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use JetBrains\PhpStorm\ArrayShape;
use Swift\Cache\CacheFactory;
use Swift\Configuration\ConfigurationInterface;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\ServiceLocatorInterface;
use Swift\ORM\Events\EventManager;
use Swift\ORM\Mapping\Driver\AttributeDriver;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class EntityConfigurationFactory
 * @package Swift\ORM
 */
#[Autowire]
final class EntityConfigurationFactory {

    private const CACHE_DIR = 'doctrine/' . PhpFileCache::EXTENSION;

    private Configuration $doctrineConfiguration;
    private Cache $cacheDriver;


    /**
     * EntityConfigurationFactory constructor.
     *
     * @param ConfigurationInterface $configuration
     * @param EventManager $eventManager
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @throws ORMException
     */
    public function __construct(
        private ConfigurationInterface $configuration,
        private EventManager $eventManager,
        private ServiceLocatorInterface $serviceLocator,
        private CacheFactory $cacheFactory,
    ) {
        $this->doctrineConfiguration = $this->createConfiguration();
    }

    /**
     * @return Configuration
     * @throws ORMException
     */
    private function createConfiguration(): Configuration {
        $config = Setup::createAnnotationMetadataConfiguration(
            $this->serviceLocator->getResourcePaths(),
            $this->isDevMode(),
            null,
            $this->cacheFactory->createDoctrineCache(self::CACHE_DIR),
            false
        );
        $config->setMetadataDriverImpl(
            new AttributeDriver( $this->serviceLocator->getResourcePaths() )
        );
        if ( ! $config->getMetadataDriverImpl() ) {
            throw ORMException::missingMappingDriverImpl();
        }

        return $config;
    }

    public function isDevMode(): bool {
        return $this->configuration->get( 'app.mode', 'app' ) === 'develop';
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration {
        return $this->doctrineConfiguration;
    }

    /**
     * @return array
     */
    #[ArrayShape( [ 'driver' => "string", 'string' => "string", 'user' => "string", 'password' => "string", 'host' => "string", 'port' => "int" ] )]
    public function getConnectionConfig(): array {
        return array(
            'driver'   => $this->configuration->get( 'connection.driver', 'database' ),
            'dbname'   => $this->configuration->get( 'connection.database', 'database' ),
            'user'     => $this->configuration->get( 'connection.username', 'database' ),
            'password' => $this->configuration->get( 'connection.password', 'database' ),
            'host'     => $this->configuration->get( 'connection.host', 'database' ),
            'port'     => $this->configuration->get( 'connection.port', 'database' ),
        );
    }

    /**
     * @return EventManager
     */
    public function getEventManager(): EventManager {
        return $this->eventManager;
    }

}