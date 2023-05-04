<?php declare(strict_types=1);

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
 * @template T
 * @template-covariant U
 */
interface ArrayCollectionInterface extends \SeekableIterator, \ArrayAccess, \Serializable, \Countable
{
    /**
     * @param \Swift\Orm\Dbal\EntityResultInterface<T>[] $values
     * @return void
     */
    public function addMany(array $values): void;
    
    /**
     * Get count of results in set.
     *
     * @return int
     */
    public function getCount(): int;
    
    /**
     * @return T|null
     */
    public function getFirst(): EntityResultInterface|null;
    
    /**
     * @return T|null
     */
    public function getLast(): EntityResultInterface|null;
    
    /**
     * Applies a mapping function to each item in the collection and returns a new collection.
     *
     * @template V
     * @param callable(T):V $callback
     * @return ArrayCollectionInterface<V>
     */
    public function map(callable $callback): self;
    
    /**
     * Filters items in the collection using a predicate function and returns a new collection.
     *
     * @param callable(T):bool $predicate
     * @return ArrayCollectionInterface<T>
     */
    public function filter(callable $predicate): self;
    
    /**
     * Determines whether any item in the collection satisfies the given predicate function.
     *
     * @param callable(T):bool $predicate
     * @return bool
     */
    public function any(callable $predicate): bool;
    
    /**
     * Determines whether all items in the collection satisfy the given predicate function.
     *
     * @param callable(T):bool $predicate
     * @return bool
     */
    public function all(callable $predicate): bool;
    
}
