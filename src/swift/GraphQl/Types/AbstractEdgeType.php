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
use Swift\GraphQl\Utils;

/**
 * Class AbstractEdgeType
 * @package Swift\GraphQl\Types
 */
abstract class AbstractEdgeType implements EdgeInterface {

    /**
     * UserEdge constructor.
     *
     * @param string|int $cursor
     * @param NodeTypeInterface $node
     */
    public function __construct(
        public string|int $cursor,
        public NodeTypeInterface $node,
    ) {
    }

    /**
     * @TODO Implement this with #[Field] Attribute and linked to the right type
     */
    abstract public function getNode(): NodeTypeInterface;

    #[Field( name: 'cursor', description: 'Edge cursor' )]
    public function getCursor(): string {
        return Utils::encodeCursor($this->cursor);
    }
}