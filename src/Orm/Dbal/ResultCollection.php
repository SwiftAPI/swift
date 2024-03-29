<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Dbal;

use Swift\Dbal\Arguments\Arguments;
use Swift\DependencyInjection\Attributes\DI;
use Traversable;

/**
 * @template T
 * @template-covariant U
 */
#[DI( autowire: false )]
final class ResultCollection extends \ArrayIterator implements ResultCollectionInterface {
    
    private \Closure $queryReference;
    private \Closure $argumentsReference;
    private int $totalCount;
    private PageInfo $pageInfo;
    
    /**
     * ResultSet constructor.
     *
     * @param array<T> $array
     * @param int   $flags
     */
    public function __construct(
        array $array = [],
        int   $flags = 0,
    ) {
        parent::__construct( $array, $flags );
    }
    
    public function initialize( \Closure $queryReference, \Closure $argumentsReference, ): ResultCollectionInterface {
        $this->queryReference = $queryReference;
        $this->argumentsReference = $argumentsReference;
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function append( mixed $value ): void {
        if ( $value->getPrimaryKeyValue() ) {
            $this[ $value->getPrimaryKeyValue() ] = $value;
            
            return;
        }
        
        $this[] = $value;
    }
    
    /**
     * @inheritDoc
     */
    public function addMany( array $values ): void {
        foreach ( $values as $value ) {
            $this[] = $value;
        }
    }
    
    /**
     * Get count of results in set
     *
     * @return int
     */
    public function getCount(): int {
        return $this->count();
    }
    
    public function getPageInfo(): PageInfo {
        if ( ! isset( $this->pageInfo ) ) {
            $arguments = $this->getArguments();
            
            $this->pageInfo = new PageInfo(
                $this->getTotalCount(),
                $arguments->getLimit(),
                $this->count(),
                $this->getFirst()?->getPrimaryKeyValue() ?? 0,
                $this->getLast()?->getPrimaryKeyValue() ?? 0,
                $arguments->getOffset(),
            );
        }
        
        return $this->pageInfo;
    }
    
    public function getArguments(): Arguments {
        $ref = $this->argumentsReference;
        
        return $ref();
    }
    
    /**
     * Get total possible results for query (without pagination)
     *
     * @return int
     */
    public function getTotalCount(): int {
        if ( ! isset( $this->totalCount ) ) {
            $this->totalCount = $this->getQuery()->count();
        }
        
        return $this->totalCount;
    }
    
    /**
     * Get query by reference
     *
     * @return mixed
     */
    public function getQuery(): \Cycle\ORM\Select {
        $ref = $this->queryReference;
        
        return $ref();
    }
    
    /**
     * @inheritDoc
     */
    public function getFirst(): EntityResultInterface|null {
        return $this[ 0 ] ?? null;
    }
    
    /**
     * @inheritDoc
     */
    public function getLast(): EntityResultInterface|null {
        if ( $this->count() < 1 ) {
            return null;
        }
        
        return $this[ ( $this->count() - 1 ) ] ?? null;
    }
    
    /**
     * @inheritDoc
     */
    public function map( callable $callback ): self {
        $result = [];
        foreach ( $this as $key => $value ) {
            $result[ $key ] = $callback( $value, $key );
        }
        
        return new self( $result );
    }
    
    /**
     * @inheritDoc
     */
    public function filter( callable $predicate ): self {
        $result = [];
        foreach ( $this as $key => $value ) {
            if ( $predicate( $value, $key ) ) {
                $result[ $key ] = $value;
            }
        }
        
        return new self( $result );
    }
    
    /**
     * @inheritDoc
     */
    public function any( callable $predicate ): bool {
        foreach ( $this as $key => $value ) {
            if ( $predicate( $value, $key ) ) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @inheritDoc
     */
    public function all( callable $predicate ): bool {
        foreach ( $this as $key => $value ) {
            if ( ! $predicate( $value, $key ) ) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * @return \ArrayIterator<T>
     */
    public function getIterator(): Traversable {
        return new \ArrayIterator( $this );
    }
    
    public function __debugInfo(): array {
        return $this->getArrayCopy();
    }
    
}