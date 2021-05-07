<?php declare(strict_types=1);

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
 * Class Tree
 * @package Swift\Configuration\Tree
 */
class Tree implements TreeInterface {

    private NodeBuilder $builder;

    /** @var NodeInterface[] $root */
    private array $root;

    /**
     * Tree constructor.
     *
     * @param array $tree
     */
    public function __construct(
        private array $tree,
    ) {

       foreach ($this->tree as $name => $value) {
           $this->root[$name] = new Node($value, $name);
       }
    }

    public function has( string $identifier ): bool {
        $keys = explode('.', $identifier);

        $identifier = $keys[array_key_first($keys)];
        unset($keys[array_key_first($keys)]);

        if (array_key_exists($identifier, $this->root)) {
            return true;
        }

        return $this->root[$identifier]->has(implode('.', $keys));
    }

    /**
     * @inheritDoc
     */
    public function get( string $identifier ): mixed {
        $keys = explode('.', $identifier);

        $identifier = $keys[array_key_first($keys)];
        unset($keys[array_key_first($keys)]);

        if (!array_key_exists($identifier, $this->root)) {
            throw new UnknownConfigurationKeyException(sprintf('%s not found as identifier in configuration', $identifier));
        }

        return !empty($keys) ? $this->root[$identifier]->getChildValue(implode('.', $keys)) : $this->root[$identifier]->getValue();
    }

    public function getChild( string $identifier ): NodeInterface {
        $keys = explode('.', $identifier);

        $identifier = $keys[array_key_first($keys)];
        unset($keys[array_key_first($keys)]);

        if (!array_key_exists($identifier, $this->root)) {
            throw new UnknownConfigurationKeyException(sprintf('%s not found as identifier in configuration', $identifier));
        }

        return !empty($keys) ? $this->root[$identifier]->getChild(implode('.', $keys)) : $this->root[$identifier];
    }

    /**
     * @inheritDoc
     */
    public function set( string $identifier, mixed $value ): void {
        $child = $this->getChild($identifier);
        $child->setValue($value);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array {
        $array = array();

        foreach ($this->root as $item) {
            $array[$item->getName()] = $item->getValue();
        }

        return $array;
    }


}