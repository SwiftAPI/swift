<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Runtime\Config;

use Swift\Configuration\ConfigurationScope;
use Swift\Configuration\ConfigurationScopeInterface;
use Swift\Configuration\Definition\Builder\TreeBuilder;
use Swift\Configuration\Definition\ConfigurationBuilderInterface;
use Swift\Configuration\Definition\Processor;
use Swift\Configuration\Exception\MissingConfigurationException;
use Swift\Configuration\SubScopeCollection;
use Swift\Configuration\Tree\Tree;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Yaml\Yaml;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\FileLocator;

/**
 * Class RuntimeConfig
 * @package Swift\Runtime\Config
 */
#[Autowire]
class RuntimeConfig extends ConfigurationScope implements ConfigurationScopeInterface, ConfigurationBuilderInterface {

    public string $coreFilePath = '';
    public string $appFilePath = INCLUDE_DIR . '/etc/config';
    public string $filename = 'runtime.yaml';
    
    /**
     * RuntimeConfig constructor.
     *
     * @param Yaml                                    $yaml
     * @param \Swift\Configuration\SubScopeCollection $subScopeCollection
     */
    public function __construct(
        protected Yaml $yaml,
        protected SubScopeCollection $subScopeCollection,
    ) {
        $coreFileLocator = new FileLocator([__DIR__]);
        $appFileLocator = new FileLocator([INCLUDE_DIR . '/etc/config']);

        try {
            $appConfig = $this->yaml->parseFile($appFileLocator->locate('runtime.yaml'));
        } catch (FileLocatorFileNotFoundException) {
            $defaultValues = $this->yaml->parseFile($coreFileLocator->locate('runtime.yaml'));
    
            foreach ($this->subScopeCollection->get('runtime') as $scope) {
                $defaultValues = [
                    ...$defaultValues,
                    ...$scope->getDefaultValues(),
                ];
            }
            file_put_contents(INCLUDE_DIR . '/etc/config/runtime.yaml', $this->yaml->dump($defaultValues));

            throw new MissingConfigurationException('Missing runtime configuration. An example configuration has been placed in the etc/config folder. Please validate configuration');
        }

        $processed = (new Processor())->processConfiguration($this, [$appConfig]);
        $this->runtimeConfig = new Tree( $processed );
        $this->appConfig = new Tree($appConfig);
        
        if ( ! empty( array_diff_key( $processed, $appConfig ) ) ) {
            $this->appConfigHasModified = true;
        }
    }

    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder(): \Symfony\Component\Config\Definition\Builder\TreeBuilder|TreeBuilder {
        $config = new TreeBuilder('runtime');

        $rootNode = $config->getRootNode();

        $rootNode = $rootNode
            ->children()
                ->arrayNode('runtime')
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->isRequired()->end()
                    ->end()
                ->end();
    
        foreach ($this->subScopeCollection->get('runtime') as $scope) {
            $scope->getConfigTreeBuilder( $rootNode );
        }
    
        $rootNode->end();

        return $config;
    }

    /**
     * @inheritDoc
     */
    public function getScope(): string {
        return 'runtime';
    }


}