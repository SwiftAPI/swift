<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\ORM;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\ORMException;
use Swift\Kernel\Attributes\Autowire;

/**
 * Class EntityManager
 * @package Swift\ORM
 */
#[Autowire]
class EntityManager extends \Doctrine\ORM\EntityManager {

    protected Connection $connection;

    /**
     * EntityManager constructor.
     */
    public function __construct(
        private EntityConfigurationFactory $entityConfigurationFactory,
    ) {
        $connection = $this->getActiveConnection();
        parent::__construct($connection, $this->entityConfigurationFactory->getConfiguration(), $this->entityConfigurationFactory->getEventManager());
    }

    /**
     * @return Connection
     * @throws ORMException
     */
    protected function getActiveConnection(): Connection {
        if (!isset($this->connection)) {
            $this->connection = static::createConnection($this->entityConfigurationFactory->getConnectionConfig(), $this->entityConfigurationFactory->getConfiguration(), null);
        }

        return $this->connection;
    }
}