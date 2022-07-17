<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Dbal;


use Cycle\Database\Driver\DriverInterface;
use Cycle\Database\Query\DeleteQuery;
use Cycle\Database\Query\InsertQuery;
use Cycle\Database\Query\SelectQuery;
use Cycle\Database\Query\UpdateQuery;
use Cycle\Database\StatementInterface;
use Cycle\Database\TableInterface;
use Swift\DependencyInjection\Attributes\Autowire;

#[Autowire]
class Dbal implements \Cycle\Database\DatabaseInterface {
    
    protected \Cycle\Database\DatabaseInterface $database;
    
    public function __construct(
        protected DbalProvider $dbalProvider,
    ) {
        $this->dbalProvider->database( 'default' );
    }
    
    /**
     * @inheritDoc
     */
    public function getName(): string {
        return $this->database->getName();
    }
    
    /**
     * @inheritDoc
     */
    public function getType(): string {
        return $this->database->getType();
    }
    
    public function getDriver( int $type = self::WRITE ): DriverInterface {
        return $this->database->getDriver( $type );
    }
    
    /**
     * @inheritDoc
     */
    public function withPrefix( string $prefix, bool $add = true ): \Cycle\Database\DatabaseInterface {
        return $this->database->withPrefix( $prefix, $add );
    }
    
    public function getPrefix(): string {
        return $this->database->getPrefix();
    }
    
    /**
     * @inheritDoc
     */
    public function hasTable( string $name ): bool {
        return $this->database->hasTable( $name );
    }
    
    /**
     * @inheritDoc
     */
    public function getTables(): array {
        return $this->database->getTables();
    }
    
    /**
     * @inheritDoc
     */
    public function table( string $name ): TableInterface {
        return $this->database->table( $name );
    }
    
    /**
     * @inheritDoc
     */
    public function execute( string $query, array $parameters = [] ): int {
        return $this->database->execute( $query, $parameters );
    }
    
    /**
     * @inheritDoc
     */
    public function query( string $query, array $parameters = [] ): StatementInterface {
        return $this->database->query( $query, $parameters );
    }
    
    /**
     * @inheritDoc
     */
    public function insert( string $table = '' ): InsertQuery {
        return $this->database->insert( $table );
    }
    
    /**
     * @inheritDoc
     */
    public function update( string $table = '', array $values = [], array $where = [] ): UpdateQuery {
        return $this->database->update( $table, $values, $where );
    }
    
    /**
     * @inheritDoc
     */
    public function delete( string $table = '', array $where = [] ): DeleteQuery {
        return $this->database->delete( $table, $where );
    }
    
    /**
     * @inheritDoc
     */
    public function select( mixed $columns = '*' ): SelectQuery {
        return $this->database->select( $columns );
    }
    
    /**
     * @inheritDoc
     */
    public function transaction( callable $callback, string $isolationLevel = null ): mixed {
        return $this->database->transaction( $callback, $isolationLevel );
    }
    
    /**
     * @inheritDoc
     */
    public function begin( string $isolationLevel = null ): bool {
        return $this->database->begin( $isolationLevel );
    }
    
    /**
     * @inheritDoc
     */
    public function commit(): bool {
        return $this->database->commit();
    }
    
    /**
     * @inheritDoc
     */
    public function rollback(): bool {
        return $this->database->rollback();
    }
}