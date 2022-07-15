<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Configuration;



class SecurityConfiguration implements \Swift\Configuration\ConfigurationSubScopeInterface {
    
    public function getScope(): array {
        return [ 'security' ];
    }
    
    public function getConfigTreeBuilder( \Symfony\Component\Config\Definition\Builder\NodeBuilder $builder ):  \Symfony\Component\Config\Definition\Builder\NodeBuilder {
        $builder
            ->arrayNode('graphql_access_control')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('name')->end()
                        ->arrayNode('fields')
                            ->scalarPrototype()->end()
                        ->end()
                        ->arrayNode('ip')
                            ->scalarPrototype()->end()
                        ->end()
                        ->arrayNode('roles')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
        
        return $builder;
    }
    
    public function getDefaultValues(): array {
        return [
            'graphql_access_control' => [
                [
                    'name' => 'example',
                    'fields' => [],
                    'ip' => [],
                    'roles' => [],
                ],
            ],
        ];
    }
    
}