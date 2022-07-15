<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Dbal;

use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\DatabaseManager;
use Swift\Configuration\ConfigurationInterface;
use Swift\DependencyInjection\Attributes\Autowire;

#[Autowire]
class DbalFactory {
    
    private ?\Cycle\Database\DatabaseManager $databaseManager = null;
    private ?\Cycle\Database\Config\DatabaseConfig $databaseConfig = null;
    
    public function __construct(
        private readonly ConfigurationInterface $configuration,
    ) {
    }
    
    public function getDatabaseManager(): DatabaseManager {
        if (!$this->databaseManager) {
            $this->databaseManager =  new \Cycle\Database\DatabaseManager( $this->getDatabaseConfig() );
        }
        
        return $this->databaseManager;
    }
    
    public function getDatabaseConfig(): DatabaseConfig {
        if (!$this->databaseConfig) {
            $this->databaseConfig = new \Cycle\Database\Config\DatabaseConfig(
                [
                    'default'     => 'default',
                    'databases'   => [
                        'default' => [
                            'connection' => $this->configuration->get( 'connection.driver', 'database' ),
                            'prefix'     => $this->configuration->get( 'connection.prefix', 'database' ),
                            'engine'     => $this->configuration->get( 'connection.engine', 'database' ),
                        ],
                    ],
                    'connections' => [
                        'mysql' => new \Cycle\Database\Config\MySQLDriverConfig(
                            connection: new \Cycle\Database\Config\MySQL\TcpConnectionConfig(
                                            database: $this->configuration->get( 'connection.database', 'database' ),
                                            host:     $this->configuration->get( 'connection.host', 'database' ),
                                            port:     (int) $this->configuration->get( 'connection.port', 'database' ),
                                            user:     $this->configuration->get( 'connection.username', 'database' ),
                                            password: $this->configuration->get( 'connection.password', 'database' ),
                                        ),
                            queryCache: true,
                        ),
                    ],
                ]
            );
        }
        
        return $this->databaseConfig;
    }
    
}