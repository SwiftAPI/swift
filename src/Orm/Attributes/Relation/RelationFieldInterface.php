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

interface RelationFieldInterface {
    
    public function getRelationType(): EntityRelationType;
    
    public function getTargetEntity(): string;
    
    public function getInverse(): ?Inverse;
    
    public function isNullable(): bool;
    
}