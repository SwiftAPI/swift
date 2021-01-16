<?php declare(strict_types=1);

namespace Swift\Database;

use Dibi\Connection;
use Dibi\Exception;
use Swift\Configuration\Configuration;
use Swift\Model\Exceptions\DatabaseException;

class DatabaseDriver extends Connection
{
	/** @var Configuration $configuation */
	protected Configuration $configuation;

	/** @var string|null $prefix  table prefix */
	protected string|null $prefix;

    /**
     * DatabaseDriver constructor.
     *
     * @param Configuration $configuration
     *
     */
	public function __construct(
		Configuration $configuration
	) {
		$this->configuation = $configuration;
		$this->prefix       = $configuration->get('database.prefix');
		$config = array(
				'driver'    => $configuration->get('database.driver'),
				'host'      => $configuration->get('database.host'),
				'username'  => $configuration->get('database.username'),
				'password'  => $configuration->get('database.password'),
				'database'  => $configuration->get('database.database'),
				'port'      => intval($configuration->get('database.port')),
		);

		try {
            parent::__construct($config);
        } catch (Exception $exception) {
		    throw new DatabaseException($exception->getMessage(), $exception->getCode(), $exception);
        }
	}

	/**
	 * Method to get table prefix
	 *
	 * @return string
	 */
	public function getPrefix() : string {
		return $this->prefix;
	}

}