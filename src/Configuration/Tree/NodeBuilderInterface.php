<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration\Tree;

/**
 * Interface NodeBuilderInterface
 * @package Swift\Configuration\Tree
 */
interface NodeBuilderInterface {

    /**
     * Set the node the builder will manage.
     *
     * @param NodeInterface $node
     *
     * @return NodeBuilderInterface The current instance
     */
    public function setNode(NodeInterface $node);

    /**
     * Get the node the builder manages.
     *
     * @return NodeInterface
     */
    public function getNode();

    /**
     * Set the value of the underlaying node.
     *
     * @param mixed $value
     *
     * @return NodebuilderInterface The current instance
     */
    public function value($value);

    /**
     * Add a leaf to the node.
     *
     * @param mixed $value The value of the leaf node
     * @param string $name
     *
     * @return NodeBuilderInterface The current instance
     */
    public function leaf( mixed $value, string $name);

    /**
     * Add several leafs to the node.
     *
     * @param $value, ... An arbitrary long list of values
     *
     * @return NodeBuilderInterface The current instance
     */
    public function leafs(array $values);

    /**
     * Add a child to the node enter in its scope.
     *
     * @param null $value
     * @param string $name
     *
     * @return NodeBuilderInterface A NodeBuilderInterface instance linked to the child node
     */
    public function tree(mixed $value, string $name);

    /**
     * Goes up to the parent node context.
     *
     * @return null|NodeBuilderInterface A NodeBuilderInterface instanced linked to the parent node
     */
    public function end();

    /**
     * Return a node instance set with the given value. Implementation can follow their own logic
     * in choosing the NodeInterface implmentation taking into account the value.
     *
     * @param mixed $value
     * @param string $name
     *
     * @return NodeInterface
     */
    public function nodeInstanceByValue(mixed $value, string $name);

}