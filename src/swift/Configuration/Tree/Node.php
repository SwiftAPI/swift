<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration\Tree;

/**
 * Class Node
 * @package Swift\Configuration\Tree
 */
class Node implements NodeInterface {

    use NodeTrait;

    /**
     * @param mixed $value
     * @param string|int|float $name
     * @param NodeInterface[] $children
     */
    public function __construct( mixed $value, string|int|float $name, array $children = [] ) {
        $this->setValue( $value, false );
        $this->setName( $name );

        if (is_array($value)) {
            foreach ($value as $nameItem => $valueItem) {
                $this->addChild(new Node($valueItem, $nameItem));
            }
        }

        if ( ! empty( $children ) ) {
            $this->setChildren( $children );
        }
    }

}