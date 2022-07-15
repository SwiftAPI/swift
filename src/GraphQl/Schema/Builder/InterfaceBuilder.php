<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Schema\Builder;


use GraphQL\Type\Definition\InterfaceType;

class InterfaceBuilder extends ObjectBuilder {
    
    /** @var callable|null */
    private $resolveType;
    
    /**
     * @return $this
     */
    public function setResolveType( callable $resolveType ): self {
        $this->resolveType = $resolveType;
        
        return $this;
    }
    
    public function build(): array {
        $parameters                  = parent::build();
        $parameters[ 'resolveType' ] = $this->resolveType;
        
        return $parameters;
    }
    
    public function buildType(): InterfaceType {
        return new InterfaceType( $this->build() );
    }
    
    
}