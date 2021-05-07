<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model;

use Dibi\Fluent;
use Swift\Kernel\Attributes\DI;
use Swift\Model\Entity\Arguments;

/**
 * Class ResultSet
 * @package Swift\Model
 */
#[DI(autowire: false)]
final class ResultSet extends \ArrayIterator {

    private int $totalCount;
    private PageInfo $pageInfo;

    /**
     * ResultSet constructor.
     *
     * @param \Closure $entityReference
     * @param \Closure $queryReference
     * @param \Closure $stateReference
     * @param \Closure $argumentsReference
     * @param array $array
     * @param int $flags
     */
    public function __construct(
        private \Closure $entityReference,
        private \Closure $queryReference,
        private \Closure $stateReference,
        private \Closure $argumentsReference,
        array $array = array(),
        int $flags = 0
    ) {
        parent::__construct($array, $flags);
    }

    /**
     * Get count of results in set
     *
     * @return int
     */
    public function getCount(): int {
        return $this->count();
    }

    /**
     * Get total possible results for query (without pagination)
     *
     * @return int
     */
    public function getTotalCount(): int {
        if (!isset($this->totalCount)) {
            $this->totalCount = $this->getQuery()->count();
        }

        return $this->totalCount;
    }

    public function getPageInfo(): PageInfo {
        if (!isset($this->pageInfo)) {
            $arguments = $this->getArguments();

            $this->pageInfo = new PageInfo(
                $this->getTotalCount(),
                $arguments->limit,
                $this->count(),
                $this->getFirst()?->getPrimaryKeyValue() ?? 0,
                $this->getLast()?->getPrimaryKeyValue() ?? 0,
                $arguments->offset,
            );
        }

        return $this->pageInfo;
    }

    public function getFirst(): Result|null {
        return $this[0] ?? null;
    }

    public function getLast(): Result|null {
        if ($this->count() < 1) {
            return null;
        }

        return $this[($this->count() - 1)] ?? null;
    }

    /**
     * Get entity by callback reference
     *
     * @return EntityInterface
     */
    public function getEntity(): EntityInterface {
        $ref = $this->entityReference;

        return $ref();
    }

    /**
     * Get query by reference
     *
     * @return mixed
     */
    public function getQuery(): Fluent {
        $ref = $this->queryReference;

        return $ref();
    }

    private function getState(): array {
        $ref = $this->stateReference;

        return $ref();
    }

    public function getArguments(): Arguments {
        $ref = $this->argumentsReference;

        return $ref();
    }

}