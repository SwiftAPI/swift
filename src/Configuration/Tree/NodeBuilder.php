<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration\Tree;


/**
 * Class NodeBuilder
 * @package Swift\Configuration\Tree
 */
class NodeBuilder implements NodeBuilderInterface {

    /**
     * @var NodeInterface[]
     */
    private array $nodeStack = [];

    public function __construct( ?NodeInterface $node = null ) {
        $this->setNode( $node ?: $this->nodeInstanceByValue(null, '') );
    }

    public function setNode( NodeInterface $node ) {
        $this
            ->emptyStack()
            ->pushNode( $node );

        return $this;
    }

    public function getNode() {
        return $this->nodeStack[ \count( $this->nodeStack ) - 1 ];
    }

    public function leaf( mixed $value, string $name ) {
        $this->getNode()->addChild(
            $this->nodeInstanceByValue( $value, $name )
        );

        return $this;
    }

    public function leafs( array $values ) {
        foreach ( $values as $name => $value ) {
            $this->leaf( $value, $name );
        }

        return $this;
    }

    public function tree( $value, $name ) {
        $node = $this->nodeInstanceByValue( $value, $name );
        $this->getNode()->addChild( $node );
        $this->pushNode( $node );

        return $this;
    }

    public function end() {
        $this->popNode();

        return $this;
    }

    public function nodeInstanceByValue( $value, string $name ) {
        return new Node( $value, $name );
    }

    public function value( $value ) {
        $this->getNode()->setValue( $value );

        return $this;
    }

    private function emptyStack() {
        $this->nodeStack = [];

        return $this;
    }

    private function pushNode( NodeInterface $node ) {
        $this->nodeStack[] = $node;

        return $this;
    }

    private function popNode() {
        return \array_pop( $this->nodeStack );
    }

}