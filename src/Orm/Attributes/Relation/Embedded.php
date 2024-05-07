<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Attributes\Relation;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use JetBrains\PhpStorm\ExpectedValues;
use Swift\DependencyInjection\Attributes\DI;

#[DI( autowire: false )]
#[Attribute( Attribute::TARGET_PROPERTY )]
#[NamedArgumentConstructor]
final class Embedded extends Relation {
    
    protected const TYPE = 'embedded';
    protected ?string $embeddedPrefix = null;
    
    /**
     * @param non-empty-string      $target Entity to embed.
     * @param non-empty-string      $load   Relation load approach.
     * @param non-empty-string|null $prefix Prefix for embedded entity columns.
     */
    public function __construct(
        string  $target,
        protected bool $empty = false,
        #[ExpectedValues( values: [ 'lazy', 'eager' ] )]
        string  $load = 'eager',
        ?string $prefix = null,
    ) {
        $this->embeddedPrefix = $prefix;
        
        parent::__construct( $target, $load );
    }
    
    public function getInverse(): ?Inverse {
        return null;
    }
    
    public function getPrefix(): ?string {
        return $this->embeddedPrefix;
    }
    
    public function setPrefix( string $prefix ): self {
        $this->embeddedPrefix = $prefix;
        
        return $this;
    }
    
    public function isNullable(): bool {
        return $this->empty;
    }
    
    
}
