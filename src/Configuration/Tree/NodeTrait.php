<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Configuration\Tree;

use Swift\Configuration\Exception\UnknownConfigurationKeyException;

/**
 * Trait NodeTrait
 * @package Swift\Configuration\Tree
 */
trait NodeTrait {

    /**
     * @var mixed
     */
    private mixed $value;

    private string|int|float $name;

    private string $type;

    /**
     * parent.
     *
     * @var NodeInterface|null
     */
    private NodeInterface|null $parent;

    /**
     * @var NodeInterface[]
     */
    private array $children = [];

    public function has( string $identifier ): bool {
        if ($identifier === $this->getName()) {
            return true;
        }

        $keys = explode('.', $identifier);

        $identifier = $keys[array_key_first($keys)];
        unset($keys[array_key_first($keys)]);

        foreach ($this->getChildren() as $child) {
            if ($identifier === $child->getName()) {
                return !empty($keys) ? $child->has(implode('.', $keys)) : true;
            }
        }

        return false;
    }

    public function setValue( mixed $value, bool $iterateChildren = true ): static {
        $this->value = $value;

        if (is_array($value) && $iterateChildren) {
            $this->removeAllChildren();
            foreach ($value as $childName => $childValue) {
                $this->addChild(new Node($childValue, $childName));
            }
        }

        return $this;
    }

    public function getValue(): mixed {
        if (!empty($this->getChildren())) {
            $value = array();

            foreach ($this->getChildren() as $child) {
                $value[$child->getName()] = $child->getValue();
            }

            return $value;
        }

        return $this->value;
    }

    /**
     * @return string
     */
    public function getName(): string|int|float {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName( string|int|float $name ): void {
        $this->name = $name;
    }

    public function addChild( NodeInterface $child ): static {
        $child->setParent( $this );
        $this->children[] = $child;

        return $this;
    }

    public function removeChild( NodeInterface $child ): static {
        foreach ( $this->children as $key => $myChild ) {
            if ( $child === $myChild ) {
                unset( $this->children[ $key ] );
            }
        }

        $this->children = \array_values( $this->children );

        $child->setParent( null );

        return $this;
    }

    public function removeAllChildren(): static {
        $this->setChildren( [] );

        return $this;
    }

    public function setParent( ?NodeInterface $parent = null ): void {
        $this->parent = $parent;
    }

    public function getAncestorsAndSelf(): array {
        return \array_merge( $this->getAncestors(), [ $this ] );
    }

    public function getAncestors(): array {
        $parents = [];
        $node    = $this;

        while ( $parent = $node->getParent() ) {
            \array_unshift( $parents, $parent );
            $node = $parent;
        }

        return $parents;
    }

    public function getParent(): NodeInterface {
        return $this->parent;
    }

    public function getNeighbors(): array {
        $neighbors = $this->getParent()->getChildren();
        $current   = $this;

        return \array_values(
            \array_filter(
                $neighbors,
                static function ( $item ) use ( $current ) {
                    return $item !== $current;
                }
            )
        );
    }

    public function getNeighborsAndSelf(): array {
        return $this->getParent()->getChildren();
    }

    public function isChild(): bool {
        return null !== $this->getParent();
    }

    /**
     * Find the root of the node.
     *
     * @return NodeInterface|NodeTrait
     */
    public function root(): NodeInterface|NodeTrait {
        $node = $this;

        while ( $parent = $node->getParent() ) {
            $node = $parent;
        }

        return $node;
    }

    /**
     * Return the distance from the current node to the root.
     *
     * Warning, can be expensive, since each descendant is visited
     *
     * @return int
     */
    public function getDepth(): int {
        if ( $this->isRoot() ) {
            return 0;
        }

        return $this->getParent()->getDepth() + 1;
    }

    /**
     * @return bool
     */
    public function isRoot(): bool {
        return null === $this->getParent();
    }

    /**
     * Return the height of the tree whose root is this node.
     *
     * @return int
     */
    public function getHeight(): int {
        if ( $this->isLeaf() ) {
            return 0;
        }

        $heights = [];

        foreach ( $this->getChildren() as $child ) {
            $heights[] = $child->getHeight();
        }

        return \max( $heights ) + 1;
    }

    public function isLeaf(): bool {
        return 0 === \count( $this->children );
    }

    public function getChild( string $identifier ): NodeInterface {
        $keys = explode('.', $identifier);

        $identifier = $keys[array_key_first($keys)];
        unset($keys[array_key_first($keys)]);

        foreach ($this->getChildren() as $child) {
            if ($identifier === $child->getName()) {
                return !empty($keys) ? $child->getChild(implode('.', $keys)) : $child;
            }
        }

        throw new UnknownConfigurationKeyException(sprintf('%s not found as identifier in configuration', $identifier));
    }

    public function getChildValue( string $identifier ): mixed {
       return $this->getChild($identifier)->getValue();
    }

    public function getChildren(): array {
        return $this->children;
    }

    public function setChildren( array $children ): static {
        $this->removeParentFromChildren();
        $this->children = [];

        foreach ( $children as $child ) {
            $this->addChild( $child );
        }

        return $this;
    }

    /**
     * Return the number of nodes in a tree.
     *
     * @return int
     */
    public function getSize(): int {
        $size = 1;

        foreach ( $this->getChildren() as $child ) {
            $size += $child->getSize();
        }

        return $size;
    }

    private function removeParentFromChildren(): void {
        foreach ( $this->getChildren() as $child ) {
            $child->setParent( null );
        }
    }

}