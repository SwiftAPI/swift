<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Attributes\Relation;

use Swift\Orm\Mapping\Definition\Relation\EntityRelationType;

abstract class Relation implements RelationInterface, RelationFieldInterface {
    
    // relation type
    protected const TYPE = '';
    
    /**
     * @param non-empty-string|null $target
     * @param non-empty-string      $load
     */
    public function __construct(
        protected ?string $target,
        protected string  $load = 'lazy',
    ) {
    }
    
    public function getType(): string {
        return static::TYPE;
    }
    
    public function getTarget(): ?string {
        return $this->target;
    }
    
    public function getLoad(): ?string {
        return $this->load;
    }
    
    public function getOptions(): array {
        $options = get_object_vars( $this );
        unset( $options[ 'target' ], $options[ 'inverse' ] );
        
        return $options;
    }
    
    public function getTargetEntity(): string {
        return $this->getTarget();
    }
    
    public function getRelationType(): EntityRelationType {
        return EntityRelationType::EMBEDDED;
    }
    
}
