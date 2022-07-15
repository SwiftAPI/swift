<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Cron\Configuration;


class Configuration implements \Swift\Configuration\ConfigurationSubScopeInterface {
    
    public function getScope(): array {
        return [ 'runtime' ];
    }
    
    public function getConfigTreeBuilder( \Symfony\Component\Config\Definition\Builder\NodeBuilder $builder ): \Symfony\Component\Config\Definition\Builder\NodeBuilder {
        $builder
            ->arrayNode( 'cron' )
                ->children()
                    ->booleanNode( 'enabled' )
                        ->defaultTrue()
                    ->end()
            
                    ->booleanNode('run_in_background')
                        ->defaultTrue()
                    ->end()
                ->end()
            ->end();
        
        return $builder;
    }
    
    public function getDefaultValues(): array {
        return [
            'cron' => [
                'enabled'    => true,
                'run_in_background' => true,
            ],
        ];
    }
    
}