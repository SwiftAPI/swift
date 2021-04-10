<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Database;

use Dibi\Connection;
use Dibi\Exception;
use Swift\Configuration\Configuration;
use Swift\Kernel\Attributes\Autowire;
use Swift\Model\Exceptions\DatabaseException;

/**
 * Class DatabaseDriver
 * @package Swift\Database
 */
#[Autowire]
class DatabaseDriver extends Connection {

    /** @var Configuration $configuration */
    protected Configuration $configuration;

    /** @var string|null $prefix table prefix */
    protected string|null $prefix;

    /**
     * DatabaseDriver constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(
        Configuration $configuration
    ) {
        $this->configuration = $configuration;
        $this->prefix        = $configuration->get( 'connection.prefix', 'database' );
        $config              = array(
            'driver'   => $configuration->get( 'connection.driver', 'database' ),
            'host'     => $configuration->get( 'connection.host', 'database' ),
            'username' => $configuration->get( 'connection.username', 'database' ),
            'password' => $configuration->get( 'connection.password', 'database' ),
            'database' => $configuration->get( 'connection.database', 'database' ),
            'port'     => (int) $configuration->get( 'connection.port', 'database' ),
        );

        try {
            parent::__construct( $config );
        } catch ( Exception $exception ) {
            throw new DatabaseException( $exception->getMessage(), $exception->getCode(), $exception );
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

}