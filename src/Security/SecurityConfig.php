<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security;

use Swift\Configuration\ConfigurationScope;
use Swift\Configuration\ConfigurationScopeInterface;
use Swift\Configuration\Definition\Builder\TreeBuilder;
use Swift\Configuration\Definition\ConfigurationBuilderInterface;
use Swift\Configuration\Definition\Processor;
use Swift\Configuration\Tree\Tree;
use Swift\Kernel\Attributes\Autowire;
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
     * SecurityConfig constructor.
     *
     * @param Yaml $yaml
     */
    public function __construct(
        protected Yaml $yaml,
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

        $this->runtimeConfig = new Tree((new Processor())->processConfiguration($this, [$coreConfig, $appConfig]));
        $this->appConfig = new Tree($appConfig);

        foreach ($this->runtimeConfig->getChild('role_hierarchy')->getChildren() as $role) {
            $role->setValue(array_unique($role->getValue()));
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

            ->end();


        return $config;
    }

}