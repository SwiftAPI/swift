<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel;

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
 * Class AppConfiguration
 * @package Swift\Kernel
 */
#[Autowire]
class AppConfiguration extends ConfigurationScope implements ConfigurationScopeInterface, ConfigurationBuilderInterface {

    public string $coreFilePath = __DIR__;
    public string $appFilePath = INCLUDE_DIR . '/etc/config';
    public string $filename = 'app.yaml';

    /**
     * AppConfiguration constructor.
     *
     * @param Yaml $yaml
     */
    public function __construct(
        protected Yaml $yaml,
    ) {
        $coreFileLocator = new FileLocator([__DIR__]);
        $appFileLocator = new FileLocator([INCLUDE_DIR . '/etc/config']);

        try {
            $appConfig = $this->yaml->parseFile($appFileLocator->locate('app.yaml'));
        } catch (FileLocatorFileNotFoundException) {
            file_put_contents(INCLUDE_DIR . '/etc/config/app.yaml', $this->yaml->dump($this->yaml->parseFile($coreFileLocator->locate('app.yaml'))));

            throw new MissingConfigurationException('Missing app configuration. An example configuration has been placed in the etc/config(/app.yaml) folder. Please configure a valid connection');
        }

        $this->runtimeConfig = new Tree((new Processor())->processConfiguration($this, [$appConfig]));
        $this->appConfig = new Tree($appConfig);
    }

    /**
     * @inheritDoc
     */
    public function getScope(): string|array {
        return array('app', 'root');
    }

    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder() {
        $config = new TreeBuilder('security');

        $rootNode = $config->getRootNode();

        $rootNode
            ->children()

                ->arrayNode('app')
                ->children()
                    ->scalarNode('name')->defaultValue('Swift')->end()
                    ->enumNode('mode')->defaultValue('develop')->values(['develop', 'production'])->end()
                    ->booleanNode('debug')->defaultTrue()->end()
                    ->booleanNode('allow_cors')->defaultTrue()->end()
                    ->scalarNode('timezone')->defaultValue('Europe/Amsterdam')->end()
                ->end()
                ->end()

                ->arrayNode('routing')
                ->children()
                    ->scalarNode('baseurl')->end()
                ->end()
                ->end()

                ->arrayNode('graphql')
                ->children()
                    ->booleanNode('enabled')->defaultTrue()->end()
                    ->booleanNode('enable_introspection')->defaultTrue()->end()
                ->end()
                ->end()

                ->arrayNode('logging')
                ->children()
                    ->booleanNode('enable_mail')->defaultTrue()->end()
                    ->scalarNode('logging_mail_from')->end()
                    ->scalarNode('logging_mail_to')->end()
                ->end()
                ->end()

            ->end();

        return $config;
    }
}