<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model;

use Swift\Kernel\Attributes\DI;

/**
 * Class PageInfo
 * @package Swift\Model
 */
#[DI(autowire: false)]
class PageInfo {

    /**
     * PageInfo constructor.
     *
     * @param int $totalCount
     * @param int $pageSize
     * @param int $currentPageSize
     * @param int $startId
     * @param int $endId
     * @param int|null $offset
     */
    public function __construct(
        private int $totalCount,
        private int $pageSize,
        private int $currentPageSize,
        private int $startId,
        private int $endId,
        private int|null $offset,
    ) {
        $this->offset ??= 0;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int {
        return $this->totalCount;
    }

    /**
     * @return int
     */
    public function getPageSize(): int {
        return $this->pageSize;
    }

    /**
     * @return int
     */
    public function getCurrentPageSize(): int {
        return $this->currentPageSize;
    }

    /**
     * @return int
     */
    public function getStartId(): int {
        return $this->startId;
    }

    /**
     * @return int
     */
    public function getEndId(): int {
        return $this->endId;
    }

    /**
     * @return int
     */
    public function getOffset(): int {
        return $this->offset;
    }

    public function hasNextPage(): bool {
        return ($this->getOffset() + $this->getCurrentPageSize()) < $this->getTotalCount();
    }

    public function hasPreviousPage(): bool {
        return $this->getOffset() > 0;
    }


}