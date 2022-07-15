<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Dbal\Driver;

use Dibi\Connection;
use Dibi\Exception;
use Dibi\Result;
use JetBrains\PhpStorm\Deprecated;
use Swift\Configuration\ConfigurationInterface;
use Swift\Dbal\Exceptions\DatabaseInitializationException;
use Swift\Dbal\QueryBuilder;
use Swift\DependencyInjection\Attributes\Autowire;

/**
 * Class DatabaseDriver
 * @package Swift\Database
 */
#[Autowire]
#[Deprecated( reason: "Use `Swift\Dbal\DatabaseDriverInterface` instead." )]
class DatabaseDriver extends Connection {
    
    /** @var string|null $prefix table prefix */
    protected string|null $prefix;
    
    /**
     * DatabaseDriver constructor.
     *
     * @param ConfigurationInterface $configuration
     */
    public function __construct(
        protected readonly ConfigurationInterface $configuration,
    ) {
        $driver = $configuration->get( 'connection.driver', 'database' );
        if ( $driver === 'mysql' ) {
            $driver = 'mysqli';
        }
        
        $this->prefix = $configuration->get( 'connection.prefix', 'database' );
        $config       = [
            'driver'   => $driver,
            'host'     => $configuration->get( 'connection.host', 'database' ),
            'username' => $configuration->get( 'connection.username', 'database' ),
            'password' => $configuration->get( 'connection.password', 'database' ),
            'database' => $configuration->get( 'connection.database', 'database' ),
            'port'     => (int) $configuration->get( 'connection.port', 'database' ),
        ];
        
        try {
            parent::__construct( $config );
        } catch ( Exception $exception ) {
            throw new DatabaseInitializationException( $exception->getMessage(), $exception->getCode(), $exception );
        }
    }
    
    /**
     * Method to get table prefix
     *
     * @return string
     */
    public function getPrefix(): string {
        return $this->prefix;
    }
    
    /**
     * @return \Swift\Dbal\QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder {
        return new QueryBuilder( $this );
    }
    
    /**
     * Batch execute an array of queries
     *
     * @param array $sqlStatements
     *
     * @return Result[]
     * @throws \Dibi\Exception
     */
    public function nativeQueries( array $sqlStatements ): array {
        $results = [];
        foreach ( $sqlStatements as $sql ) {
            $results[] = $this->nativeQuery( $sql );
        }
        
        return $results;
    }
    
    
}