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
 *
 * Class ResultSet
 * @package Swift\Orm\Collection
 */
#[DI( autowire: false )]
final class ArrayCollection extends \ArrayIterator implements ArrayCollectionInterface {
    
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
    
    /**
     * @param \Swift\Orm\Dbal\EntityResultInterface $value
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
    
    public function getFirst(): EntityResultInterface|null {
        return $this[ 0 ] ?? null;
    }
    
    public function getLast(): EntityResultInterface|null {
        if ( $this->count() < 1 ) {
            return null;
        }
        
        return $this[ ( $this->count() - 1 ) ] ?? null;
    }
    
    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable {
        return new \ArrayIterator( $this );
    }
    
    public function __debugInfo(): array {
        return $this->getArrayCopy();
    }
    
}