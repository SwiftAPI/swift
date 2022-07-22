<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Schema\Generator;

use GraphQL\Type\Definition\Type;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\GraphQl\DependencyInjection\DiTags;
use Swift\GraphQl\Schema\Builder\Builder;
use Swift\GraphQl\Schema\Registry;

#[Autowire]
class BuilderGenerator implements GeneratorInterface, ManualGeneratorInterface {
    
    /** @var \Swift\GraphQl\Schema\Definition\SchemaBuilderInterface[] $builders */
    protected array $builders = [];
    
    public function generate( \Swift\GraphQl\Schema\Registry $registry ): \Swift\GraphQl\Schema\Registry {
        
        foreach ($this->builders as $builder) {
            $builder->define( $registry );
        }
        
        
        return $registry;
    }
    
    #[Autowire]
    public function setBuilders( #[Autowire( tag: DiTags::GRAPHQL_SCHEMA_BUILDER )] ?iterable $builders ): void {
        if ( ! $builders ) {
            return;
        }
        
        $this->builders = iterator_to_array( $builders );
    }
    
}