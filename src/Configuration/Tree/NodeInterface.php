<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration\Tree;

/**
 * Interface NodeInterface
 * @package Swift\Configuration\Tree
 */
interface NodeInterface {

    /**
     * Determine whether node of childnode has given identifier
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function has( string $identifier ): bool;

    /**
     * Set the value of the current node.
     *
     * @param mixed $value
     *
     * @return NodeInterface the current instance
     */
    public function setValue($value);

    /**
     * Get the current node value.
     *
     * @return mixed
     */
    public function getValue(): mixed;

    /**
     * Add a child.
     *
     * @param NodeInterface $child
     *
     * @return mixed
     */
    public function addChild(self $child);

    /**
     * Remove a node from children.
     *
     * @param NodeInterface $child
     *
     * @return NodeInterface the current instance
     */
    public function removeChild(self $child);

    /**
     * Remove all children.
     *
     * @return NodeInterface The current instance
     */
    public function removeAllChildren();

    /**
     * @param string $identifier
     *
     * @return mixed
     */
    public function getChildValue( string $identifier ): mixed;

    /**
     * Return the array of children.
     *
     * @return NodeInterface[]
     */
    public function getChildren(): array;

    /**
     * Replace the children set with the given one.
     *
     * @param NodeInterface[] $children
     *
     * @return mixed
     */
    public function setChildren(array $children): mixed;

    /**
     * Set the parent node.
     *
     * @param NodeInterface|null $parent
     */
    public function setParent(?self $parent = null): void;

    /**
     * Return the parent node.
     *
     * @return NodeInterface|null
     */
    public function getParent(): NodeInterface|null;

    /**
     * Retrieves all ancestors of node excluding current node.
     *
     * @return NodeInterface[]
     */
    public function getAncestors(): array;

    /**
     * Retrieves all ancestors of node as well as the node itself.
     *
     * @return Node[]
     */
    public function getAncestorsAndSelf(): array;

    /**
     * Retrieves all neighboring nodes, excluding the current node.
     *
     * @return array
     */
    public function getNeighbors(): array;

    /**
     * Returns all neighboring nodes, including the current node.
     *
     * @return Node[]
     */
    public function getNeighborsAndSelf(): array;

    /**
     * Return true if the node is the root, false otherwise.
     *
     * @return bool
     */
    public function isRoot(): bool;

    /**
     * Return true if the node is a child, false otherwise.
     *
     * @return bool
     */
    public function isChild(): bool;

    /**
     * Return true if the node has no children, false otherwise.
     *
     * @return bool
     */
    public function isLeaf(): bool;

    /**
     * Return the distance from the current node to the root.
     *
     * @return int
     */
    public function getDepth(): int;

    /**
     * Return the height of the tree whose root is this node.
     *
     * @return int
     */
    public function getHeight(): int;

    public function getChild( string $implode ): NodeInterface;

    public function getName(): string|int|float;

}