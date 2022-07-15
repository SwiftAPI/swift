<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Schema\Builder;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\UnionType;


class UnionBuilder extends TypeBuilder {
    
    /** @var callable(object, mixed, ResolveInfo)|null */
    private $resolveType;
    
    /** @var ObjectType[]|null */
    private ?array $types = null;
    
    /**
     * @return static
     */
    public static function create( string $name ): self {
        return new static( $name );
    }
    
    /**
     * @param callable(mixed):ObjectType $resolveType
     *
     * @return $this
     * @see ResolveInfo Force Jetbrains IDE use
     *
     */
    public function setResolveType( callable $resolveType ): self {
        $this->resolveType = $resolveType;
        
        return $this;
    }
    
    /**
     * @param ObjectType[] $types
     *
     * @return $this
     */
    public function setTypes( array $types ): self {
        $this->types = $types;
        
        return $this;
    }
    
    public function build(): array {
        $parameters                  = parent::build();
        $parameters[ 'types' ]       = $this->types;
        $parameters[ 'resolveType' ] = $this->resolveType;
        
        return $parameters;
    }
    
    public function buildType(): UnionType {
        return new UnionType( $this->build() );
    }
    
    
}