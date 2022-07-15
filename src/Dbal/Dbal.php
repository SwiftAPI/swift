<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Dbal;


use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Swift\DependencyInjection\Attributes\Autowire;

#[Autowire]
class Dbal implements DatabaseProviderInterface, LoggerAwareInterface {
    
    protected \Cycle\Database\DatabaseManager $database;
    
    public function __construct(
        protected DbalFactory $dbalFactory,
    ) {
        $this->database = $this->dbalFactory->getDatabaseManager();
    }
    
    public function __call( string $name, array $args ): mixed {
        return $this->database->{$name}( $args );
    }
    
    /**
     * @inheritDoc
     */
    public function database( string $database = null ): DatabaseInterface {
        return $this->database->database( $database );
    }
    
    /**
     * @inheritDoc
     */
    public function setLogger( LoggerInterface $logger ): void {
        $this->database->setLogger( $logger );
    }
    
}