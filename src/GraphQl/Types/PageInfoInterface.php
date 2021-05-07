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
use Swift\GraphQl\Attributes\InterfaceType;

/**
 * Interface PageInfoInterface
 * @package Swift\GraphQl\Types
 */
#[InterfaceType(name: 'PageInfoInterface', description: 'Information about pagination in a connection')]
interface PageInfoInterface {

    #[Field(name: 'total', description: 'Total number of items available')]
    public function getTotal(): int;

    #[Field(name: 'endCursor', description: 'When paginating forwards, the cursor to continue')]
    public function getEndCursor(): string;

    #[Field(name: 'hasPreviousPage', description: 'When paginating backwards, are there more items?')]
    public function hasPreviousPage(): bool;

    #[Field(name: 'hasNextPage', description: 'When paginating forwards, are there more items?')]
    public function hasNextPage(): bool;

    #[Field(name: 'startCursor', description: 'When paginating backwards, the cursor to continue')]
    public function getStartCursor(): string;

}