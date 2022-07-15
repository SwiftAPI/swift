<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Executor\Resolver;

use Swift\Code\ReflectionFactory;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\GraphQl\Attributes\Resolve;
use Swift\GraphQl\DependencyInjection\DiTags;

#[Autowire]
class ResolverCollector {
    
    protected bool $compiled = false;
    protected array $resolvers = [];
    protected array $resolverMap = [];
    
    public function __construct(
        protected ReflectionFactory $factory,
    ) {
    }
    
    
    public function get( string $type ): ?array {
        if ( ! $this->compiled ) {
            $this->compile();
        }
        
        return $this->resolverMap[ $type ] ?? null;
    }
    
    protected function compile(): void {
        foreach ( $this->resolvers as $resolver ) {
            foreach ( $this->factory->getReflectionClass( $resolver )->getMethods() as $method ) {
                if ( ! $method->isPublic() || $method->isStatic() ) {
                    continue;
                }
                
                $attribute = $this->factory->getAttributeReader()->getMethodAnnotation( $method, Resolve::class );
                
                if ( ! $attribute ) {
                    continue;
                }
                
                if ( ! isset( $this->resolverMap[ $attribute->getName() ] ) ) {
                    $this->resolverMap[ $attribute->getName() ] = [];
                }
                
                $this->resolverMap[ $attribute->getName() ][] = [ $resolver, $method->getName() ];
            }
        }
        
        $this->compiled = true;
    }
    
    
    #[Autowire]
    public function setResolvers( #[Autowire( tag: DiTags::GRAPHQL_RESOLVER )] ?iterable $resolvers ): void {
        if ( ! $resolvers ) {
            return;
        }
        
        $this->resolvers = iterator_to_array( $resolvers );
    }
    
}