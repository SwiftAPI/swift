<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem\Watcher\Configuration;


class Configuration implements \Swift\Configuration\ConfigurationSubScopeInterface {
    
    public function getScope(): array {
        return [ 'runtime' ];
    }
    
    public function getConfigTreeBuilder( \Symfony\Component\Config\Definition\Builder\NodeBuilder $builder ): \Symfony\Component\Config\Definition\Builder\NodeBuilder {
        $builder
            ->arrayNode( 'file_watcher' )
                ->children()
                    ->booleanNode( 'enabled' )
                        ->defaultTrue()
                    ->end()
                    ->arrayNode('watch')
                        ->scalarPrototype()->end()
                    ->end()
    
                    ->arrayNode('extensions')
                        ->scalarPrototype()->end()
                    ->end()
            
                    ->arrayNode('ignore')
                        ->scalarPrototype()->end()
                    ->end()
            
                ->end()
            ->end();
        
        return $builder;
    }
    
    public function getDefaultValues(): array {
        return [
            'file_watcher' => [
                'enabled'    => true,
                'watch'      => [ 'app', 'src', 'etc/config' ],
                'extensions' => [ 'php', 'yaml' ],
                'ignore'     => [ 'tests' ],
            ],
        ];
    }
    
}