<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Attributes;


use Swift\DependencyInjection\Attributes\DI;
use Swift\Orm\Mapping\Definition\IndexType;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
#[DI(autowire: false)]
class Index {
    
    public function __construct(
        protected readonly array $fields,
        protected readonly bool $unique = false,
    ) {
    }
    
    /**
     * @return array
     */
    public function getFields(): array {
        return $this->fields;
    }
    
    /**
     * @return \Swift\Orm\Mapping\Definition\IndexType
     */
    public function getType(): IndexType {
        return $this->unique ? IndexType::UNIQUE : IndexType::INDEX;
    }
    
    
    
}