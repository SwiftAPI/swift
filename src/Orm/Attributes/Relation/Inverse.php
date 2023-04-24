<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Attributes\Relation;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Swift\DependencyInjection\Attributes\DI;
use Swift\Orm\Mapping\Definition\Relation\EntityRelationType;

#[\Attribute(\Attribute::TARGET_PROPERTY), DI(autowire: false)]
#[NamedArgumentConstructor]
#[\AllowDynamicProperties]
class Inverse {
    
    public function __construct(
        private readonly string             $as,
        private readonly EntityRelationType $type,
    ) {
    }
    
    /**
     * @return string
     */
    public function getAs(): string {
        return $this->as;
    }
    
    /**
     * @return \Swift\Orm\Mapping\Definition\Relation\EntityRelationType
     */
    public function getRelationType(): EntityRelationType {
        return $this->type;
    }
    
}