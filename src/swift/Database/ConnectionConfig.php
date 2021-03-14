<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Database;

use Swift\Configuration\ConfigurationScope;
use Swift\Configuration\ConfigurationScopeInterface;
use Swift\Configuration\Definition\Builder\TreeBuilder;
use Swift\Configuration\Definition\ConfigurationBuilderInterface;
use Swift\Configuration\Definition\Processor;
use Swift\Configuration\Exception\MissingConfigurationException;
use Swift\Configuration\Tree\Tree;
use Swift\Kernel\Attributes\Autowire;
use Swift\Yaml\Yaml;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\FileLocator;

/**
 * Class ConnectionConfig
 * @package Swift\Database
 */
#[Autowire]
class ConnectionConfig extends ConfigurationScope implements ConfigurationScopeInterface, ConfigurationBuilderInterface {

    public string $coreFilePath = '';
    public string $appFilePath = INCLUDE_DIR . '/etc/config';
    public string $filename = 'database.yaml';

    /**
     * ConnectionConfig constructor.
     *
     * @param Yaml $yaml
     */
    public function __construct(
        protected Yaml $yaml,
    ) {
        $coreFileLocator = new FileLocator([__DIR__]);
        $appFileLocator = new FileLocator([INCLUDE_DIR . '/etc/config']);

        try {
            $appConfig = $this->yaml->parseFile($appFileLocator->locate('database.yaml'));
        } catch (FileLocatorFileNotFoundException) {
            file_put_contents(INCLUDE_DIR . '/etc/config/database.yaml', $this->yaml->dump($this->yaml->parseFile($coreFileLocator->locate('database.yaml'))));

            throw new MissingConfigurationException('Missing database configuration. An example configuration has been placed in the etc/config folder. Please configure a valid connection');
        }

        $this->runtimeConfig = new Tree((new Processor())->processConfiguration($this, [$appConfig]));
        $this->appConfig = new Tree($appConfig);
    }

    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder() {
        $config = new TreeBuilder('database');

        $rootNode = $config->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('connection')
                    ->children()
                        ->scalarNode('driver')->defaultValue('mysqli')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('host')->defaultValue('localhost')->end()
                        ->scalarNode('username')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('password')->isRequired()->end()
                        ->scalarNode('database')->isRequired()->cannotBeEmpty()->end()
                        ->integerNode('port')->defaultValue(3306)->end()
                        ->scalarNode('prefix')->defaultValue('')->end()
                    ->end()
                ->end()
            ->end();

        return $config;
    }

    /**
     * @inheritDoc
     */
    public function getScope(): string {
        return 'database';
    }


}