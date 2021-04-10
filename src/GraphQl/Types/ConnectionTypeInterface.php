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
 * Interface ConnectionTypeInterface
 * @package Swift\GraphQl\Types
 */
#[InterfaceType(name: 'ConnectionInterface', description: 'A connection to a list of items.')]
interface ConnectionTypeInterface {

    #[Field(name: 'totalCount', description: 'Total count of edges in result set')]
    public function getTotalCount(): int;

    #[Field(name: 'edges', type: EdgeInterface::class, isList: true, description: 'Items in result set')]
    public function getEdges(): array;

    #[Field(name: 'pageInfo', description: 'Information to aid in pagination')]
    public function getPageInfo(): PageInfoInterface;

}