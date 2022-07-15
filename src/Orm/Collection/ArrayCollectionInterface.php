<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Collection;


use Swift\Orm\Dbal\EntityResultInterface;

/**
 * @template-covariant T
 */
interface ArrayCollectionInterface extends \SeekableIterator, \ArrayAccess, \Serializable, \Countable {
    
    /**
     * @param \Swift\Orm\Dbal\EntityResultInterface<T>[] $values
     *
     * @return void
     */
    public function addMany( array $values ): void;
    
    /**
     * Get count of results in set
     *
     * @return int
     */
    public function getCount(): int;
    
    public function getFirst(): EntityResultInterface|null;
    
    public function getLast(): EntityResultInterface|null;
    
}