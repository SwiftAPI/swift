<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Attributes\Relation;

interface RelationInterface {
    
    public function getType(): string;
    
    public function getTarget(): ?string;
    
    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array;
    
    public function getInverse(): ?Inverse;
}
