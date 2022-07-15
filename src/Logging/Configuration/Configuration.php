<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Logging\Configuration;


class Configuration implements \Swift\Configuration\ConfigurationSubScopeInterface {
    
    public function getScope(): array {
        return [ 'app', 'root' ];
    }
    
    public function getConfigTreeBuilder( \Symfony\Component\Config\Definition\Builder\NodeBuilder $builder ): \Symfony\Component\Config\Definition\Builder\NodeBuilder {
        $builder
            ->arrayNode( 'logging' )
                ->children()
                    ->booleanNode( 'enable_mail' )->defaultTrue()->end()
                    ->scalarNode( 'logging_mail_from' )->end()
                    ->scalarNode( 'logging_mail_to' )->end()
                ->end()
            ->end();
        
        return $builder;
    }
    
    public function getDefaultValues(): array {
        return [
            'logging' => [
                'enable_mail'       => false,
                'logging_mail_from' => 'log@example.com',
                'logging_mail_to'   => 'log@example.com',
            ],
        ];
    }
    
}