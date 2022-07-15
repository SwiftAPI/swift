<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Dbal;

use JetBrains\PhpStorm\Deprecated;

#[Deprecated]
class QuerySequence {
    
    /**
     * @param \Swift\Dbal\QueryBuilder[]|self $queries
     */
    public function __construct(
        private array $queries = [],
        private array $callbacks = [],
    ) {
    }
    
    public function add( string $identifier, QueryBuilder|self $query, \Closure $callback = null ): void {
        $this->queries[$identifier] = $query;
        
        if ($callback) {
            $this->callbacks[$identifier] = $callback;
        }
    }
    
    public function addToBeginning( string $identifier, QueryBuilder|self $query ): void {
        $queries = $this->queries;
        $this->queries = [
            ...[$identifier => $identifier],
            ...$queries,
        ];
    }
    
    /**
     * @param \Swift\Dbal\QueryBuilder[]|self[] $queries
     *
     * @return void
     */
    public function addMany( array $queries ): void {
        $currentQueries = $this->queries;
        $this->queries = [
            ...$currentQueries,
            ...$queries,
        ];
    }
    
    /**
     * @param \Swift\Dbal\QueryBuilder[]|self[] $queries
     *
     * @return void
     */
    public function addManyToBeginning( array $queries ): void {
        $currentQueries = $this->queries;
        $this->queries = [
            ...$currentQueries,
            ...$queries,
        ];
    }
    
    public function remove( string $identifier ): void {
        unset( $this->queries[$identifier] );
    }
    
    public function execute(): void {
        foreach ($this->queries as $key => $query) {
            $result = $query->execute( 'n' );
            
            if (isset($this->callbacks[$key])) {
                $this->callbacks[$key]($result);
            }
        }
    }
    
}