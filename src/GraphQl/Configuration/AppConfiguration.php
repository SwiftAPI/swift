<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Configuration;



class AppConfiguration implements \Swift\Configuration\ConfigurationSubScopeInterface {
    
    public function getScope(): array {
        return [ 'app', 'root' ];
    }
    
    public function getConfigTreeBuilder( \Symfony\Component\Config\Definition\Builder\NodeBuilder $builder ):  \Symfony\Component\Config\Definition\Builder\NodeBuilder {
        $builder
            ->arrayNode('graphql')
                ->children()
                    ->booleanNode('enabled')->defaultTrue()->end()
                    ->booleanNode('enable_introspection')->defaultTrue()->end()
                    ->integerNode('max_query_complexity')->defaultNull()->end()
                    ->integerNode('max_query_depth')->defaultNull()->end()
                ->end()
            ->end();
        
        return $builder;
    }
    
    public function getDefaultValues(): array {
        return [
            'graphql' => [
                'enabled'              => true,
                'enable_introspection' => true,
                'max_query_complexity' => null,
                'max_query_depth'      => null,
            ],
        ];
    }
    
}