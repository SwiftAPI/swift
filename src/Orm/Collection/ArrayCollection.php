<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Collection;

use Swift\DependencyInjection\Attributes\DI;
use Swift\Orm\Dbal\EntityResultInterface;
use Traversable;

/**
 * @template T
 * @template-covariant U
 */
#[DI( autowire: false )]
final class ArrayCollection extends \ArrayIterator implements ArrayCollectionInterface {
    
    /**
     * @param array<T> $array
     * @param int      $flags
     */
    public function __construct(
        array $array = [],
        int   $flags = 0,
    ) {
        parent::__construct( $array, $flags );
    }
    
    /**
     * @param T $value
     *
     * @return void
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
     * @inheritDoc
     */
    public function getCount(): int {
        return $this->count();
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