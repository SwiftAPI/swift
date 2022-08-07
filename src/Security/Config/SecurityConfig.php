<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Config;

use Swift\Configuration\ConfigurationScope;
use Swift\Configuration\ConfigurationScopeInterface;
use Swift\Configuration\Definition\Builder\TreeBuilder;
use Swift\Configuration\Definition\ConfigurationBuilderInterface;
use Swift\Configuration\Definition\Processor;
use Swift\Configuration\SubScopeCollection;
use Swift\Configuration\Tree\Tree;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Security\Authorization\Strategy\AffirmativeDecisionStrategy;
use Swift\Yaml\Yaml;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\FileLocator;

/**
 * Class SecurityConfig
 * @package Swift\Security\Configuration
 */
#[Autowire]
class SecurityConfig extends ConfigurationScope implements ConfigurationScopeInterface, ConfigurationBuilderInterface {

    public string $coreFilePath = __DIR__;
    public string $appFilePath = INCLUDE_DIR . '/etc/config';
    public string $filename = 'security.yaml';
    
    /**
     * @param Yaml                                    $yaml
     * @param \Swift\Configuration\SubScopeCollection $subScopeCollection
     */
    public function __construct(
        protected Yaml $yaml,
        protected SubScopeCollection $subScopeCollection,
    ) {
        $coreFileLocator = new FileLocator([__DIR__]);
        $appFileLocator = new FileLocator([INCLUDE_DIR . '/etc/config']);
        $coreConfig = $this->yaml->parseFile($coreFileLocator->locate('security.yaml'));

        try {
            $appConfig = $this->yaml->parseFile($appFileLocator->locate('security.yaml'));
        } catch (FileLocatorFileNotFoundException) {
            file_put_contents(INCLUDE_DIR . '/etc/config/security.yaml', $this->yaml->dump($this->yaml->parseFile($coreFileLocator->locate('security.yaml'))));
            $appConfig = $this->yaml->parseFile($appFileLocator->locate('security.yaml'));
        }

        $processed = (new Processor())->processConfiguration($this, [$coreConfig, $appConfig]);
        $this->runtimeConfig = new Tree($processed);
        $this->appConfig = new Tree($appConfig);

        foreach ($this->runtimeConfig->getChild('role_hierarchy')->getChildren() as $role) {
            $role->setValue(array_unique($role->getValue()));
        }
        
        if ( ! empty( array_diff_key( $processed, $appConfig ) ) ) {
            $this->appConfigHasModified = true;
        }
    }

    /**
     * @inheritDoc
     */
    public function getScope(): string {
        return 'security';
    }

    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder(): TreeBuilder {
        $config = new TreeBuilder('security');

        $rootNode = $config->getRootNode();

        $rootNode
            ->children()

                ->booleanNode('enable_firewalls')
                    ->defaultTrue()
                ->end()

                ->arrayNode('firewalls')
                    ->children()
                        ->arrayNode('main')
                            ->children()
                                ->arrayNode('login_throttling')
                                    ->children()
                                        ->integerNode('max_attempts')
                                            ->info('limit login attempts, defaults to 5 per minute. Set to 0 to disable throttling')
                                            ->defaultValue(5)
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('token')
                                    ->children()
                                        ->integerNode('validity')
                                            ->info('Default time in which a token expires in hours')
                                            ->defaultValue(24)
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('role_hierarchy')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->scalarPrototype()->end()
                    ->end()
                ->end()

                ->arrayNode('access_decision_manager')
                    ->children()
                        ->scalarNode('strategy')
                            ->defaultValue(AffirmativeDecisionStrategy::class)
                        ->end()
                        ->booleanNode('allow_if_all_abstain')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('access_control')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('path')->end()
                            ->arrayNode('ip')
                                ->scalarPrototype()->end()
                            ->end()
                            ->arrayNode('roles')
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            
                ->arrayNode( 'rate_limit' )
                    ->children()
                        ->booleanNode( 'enabled' )->defaultFalse()->end()
                        ->booleanNode( 'enable_default' )->defaultFalse()->end()
                        ->integerNode( 'default_limit' )->defaultValue( 100 )->end()
                        ->integerNode( 'default_period' )->defaultValue( 100 )->end()
                        ->scalarNode( 'default_strategy' )->defaultValue('sliding_window')->cannotBeEmpty()->end()
                        ->arrayNode('rates')
                            ->useAttributeAsKey( 'name', false )
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode( 'name' )->cannotBeEmpty()->end()
                                    ->scalarNode( 'strategy' )->defaultValue('sliding_window')->cannotBeEmpty()->end()
                                    ->integerNode( 'limit' )->defaultValue( 100 )->end()
                                    ->integerNode( 'period' )->defaultValue( 60 )->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            
            ->end();
        
        foreach ($this->subScopeCollection->get('security') as $scope) {
            $scope->getConfigTreeBuilder( $rootNode->children() );
        }

        return $config;
    }

}