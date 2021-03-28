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
#[Type(name: 'PageInfo')]
class PageInfoType extends PageInfo implements PageInfoInterface {

    #[Field(name: 'endCursor', description: 'Last cursor in current page')]
    public function getEndCursor(): string {
        return Utils::encodeCursor($this->getEndId());
    }

    #[Field(name: 'hasPreviousPage', description: 'Result set has previous page(s)')]
    public function hasPreviousPage(): bool {
        return parent::hasPreviousPage();
    }

    #[Field(name: 'hasNextPage', description: 'Result set has additional page(s)')]
    public function hasNextPage(): bool {
        return parent::hasNextPage();
    }

    #[Field(name: 'startCursor', description: 'First cursor in current page')]
    public function getStartCursor(): string {
        return Utils::encodeCursor($this->getStartId());
    }

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