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
 * Interface EdgeInterface
 * @package Swift\GraphQl\Types
 */
#[InterfaceType(name: 'EdgeInterface')]
interface EdgeInterface {

    #[Field(name: 'node', type: NodeTypeInterface::class, isList: false)]
    public function getNode(): NodeTypeInterface;

    #[Field(name: 'cursor')]
    public function getCursor(): string;

}