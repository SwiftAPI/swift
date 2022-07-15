<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Collection;


class CollectionFactory implements \Cycle\ORM\Collection\CollectionFactoryInterface {
    
    public string $class;
    
    public function __construct() {
        $this->class = ArrayCollection::class;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getInterface(): ?string {
        return ArrayCollectionInterface::class;
    }
    
    /**
     * @inheritDoc
     */
    public function withCollectionClass( string $class ): static {
        if (!is_a( $class, ArrayCollectionInterface::class, true )) {
            throw new CollectionFactoryException('Unsupported Collection type.');
        }
        
        $clone = clone $this;
        $clone->class = $class;
        
        return $clone;
    }
    
    /**
     * @inheritDoc
     */
    public function collect( iterable $data ): iterable {
        if (is_a( $data, ArrayCollectionInterface::class ) ) {
            $data = (array) $data;
        }
        
        return match (true) {
            \is_array($data) => new $this->class( $data ),
            $data instanceof \Traversable => new $this->class( \iterator_to_array($data) ),
            default => throw new CollectionFactoryException('Unsupported iterable type.'),
        };
    }
    
}