<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Types;


use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\Type;
use Swift\GraphQl\Utils;
use Swift\Model\PageInfo;

/**
 * Class PageInfoType
 * @package Swift\GraphQl\Types
 */
#[Type(name: 'PageInfo', description: 'Information about pagination in a connection')]
class PageInfoType extends PageInfo implements PageInfoInterface {

    #[Field(name: 'total', description: 'Total number of items available')]
    public function getTotal(): int {
        return $this->getTotalCount();
    }

    #[Field(name: 'endCursor', description: 'When paginating forwards, the cursor to continue')]
    public function getEndCursor(): string {
        return Utils::encodeCursor($this->getEndId());
    }

    #[Field(name: 'hasPreviousPage', description: 'When paginating backwards, are there more items?')]
    public function hasPreviousPage(): bool {
        return parent::hasPreviousPage();
    }

    #[Field(name: 'hasNextPage', description: 'When paginating forwards, are there more items?')]
    public function hasNextPage(): bool {
        return parent::hasNextPage();
    }

    #[Field(name: 'startCursor', description: 'When paginating backwards, the cursor to continue')]
    public function getStartCursor(): string {
        return Utils::encodeCursor($this->getStartId());
    }

    /**
     * Generate PageInfo to use for querying entities
     *
     * @param PageInfo $pageInfo
     *
     * @return PageInfoType
     */
    public static function fromModelPageInfo( PageInfo $pageInfo ): PageInfoType {
        return new static(
            $pageInfo->getTotalCount(),
            $pageInfo->getPageSize(),
            $pageInfo->getCurrentPageSize(),
            $pageInfo->getStartId(),
            $pageInfo->getEndId(),
            $pageInfo->getOffset(),
        );
    }


}