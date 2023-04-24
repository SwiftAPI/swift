<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Attributes;

use Attribute;
use Cycle\Annotated\Annotation\Column;
use Swift\DependencyInjection\Attributes\DI;

#[Attribute( Attribute::TARGET_CLASS )]
#[\AllowDynamicProperties]
#[DI( autowire: false )]
final class Embeddable {
    
    private readonly ?string $role;
    
    /**
     * @param string|array|null $role         Entity role. Defaults to the lowercase class name without a namespace.
     * @param class-string|null $mapper       Mapper class name. Defaults to {@see \Cycle\ORM\Mapper\Mapper}.
     * @param string            $columnPrefix Custom prefix for embeddable entity columns.
     * @param Column[]          $columns      Embedded entity columns.
     */
    public function __construct(
        null|string|array        $role = null,
        private readonly ?string $mapper = null,
        private readonly string  $columnPrefix = '',
        private readonly array   $columns = [],
    ) {
        if ( is_array( $role ) ) {
            $role = $role[ 0 ] ?? null;
        }
        $this->role = $role;
    }
    
    public function getRole(): ?string {
        return $this->role;
    }
    
    public function getMapper(): ?string {
        return $this->mapper;
    }
    
    public function getColumnPrefix(): string {
        return $this->columnPrefix;
    }
    
    public function getColumns(): array {
        return $this->columns;
    }
    
}