<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\Configuration;

use Swift\Configuration\ConfigurationScope;
use Swift\Configuration\ConfigurationScopeInterface;
use Swift\Configuration\Definition\Builder\TreeBuilder;
use Swift\Configuration\Definition\ConfigurationBuilderInterface;
use Swift\Configuration\Definition\Processor;
use Swift\Configuration\Exception\MissingConfigurationException;
use Swift\Configuration\SubScopeCollection;
use Swift\Configuration\Tree\Tree;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\FileSystem\FileSystem;
use Swift\Yaml\Yaml;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\FileLocator;

/**
 * Class AppConfiguration
 * @package Swift\Kernel\Configuration
 */
#[Autowire]
class AppConfiguration extends ConfigurationScope implements ConfigurationScopeInterface, ConfigurationBuilderInterface {

    public string $coreFilePath = __DIR__;
    public string $appFilePath = INCLUDE_DIR . '/etc/config';
    public string $filename = 'app.yaml';
    
    /**
     * AppConfiguration constructor.
     *
     * @param Yaml                                    $yaml
     * @param \Swift\Configuration\SubScopeCollection $subScopeCollection
     */
    public function __construct(
        protected Yaml $yaml,
        protected SubScopeCollection $subScopeCollection,
    ) {
         $appFileLocator = new FileLocator([INCLUDE_DIR . '/etc/config']);

        try {
            $appConfig = $this->yaml->parseFile($appFileLocator->locate('app.yaml'));
        } catch (FileLocatorFileNotFoundException) {
            $coreFileLocator = new FileLocator([__DIR__]);
            
            $defaultValues = $this->yaml->parseFile($coreFileLocator->locate('app.yaml'));
            
            foreach ($this->subScopeCollection->get('app') as $scope) {
                $defaultValues = [
                    ...$defaultValues,
                    ...$scope->getDefaultValues(),
                ];
            }
            
            (new FileSystem())->write('/etc/config/app.yaml', $this->yaml->dump($defaultValues));

            throw new MissingConfigurationException('Missing app configuration. An example configuration has been placed in the etc/config(/app.yaml) folder. Please validate whether the configuration is right for your project.');
        }

        $processed = (new Processor())->processConfiguration($this, [$appConfig]);
        $this->runtimeConfig = new Tree($processed);
        $this->appConfig = new Tree($appConfig);
    
        if ( !empty(array_diff_key( $processed, $appConfig )) ) {
            $this->appConfigHasModified = true;
        }
    }

    /**
     * @inheritDoc
     */
    public function getScope(): string|array {
        return ['app', 'root'];
    }
    
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder(): \Symfony\Component\Config\Definition\Builder\TreeBuilder|TreeBuilder {
        $config = new TreeBuilder('app');
        
        $rootNode = $config->getRootNode();
        
        $rootNode = $rootNode
            ->children()
            
            ->arrayNode('app')
                ->children()
                    ->scalarNode('name')->defaultValue('Swift')->end()
                    ->enumNode('mode')->defaultValue('develop')->values(['develop', 'production'])->end()
                    ->booleanNode('debug')->defaultTrue()->end()
                    ->booleanNode('allow_cors')->defaultTrue()->end()
                    ->scalarNode('timezone')->defaultValue('Europe/Amsterdam')->end()
                    ->booleanNode('log_requests')->defaultTrue()->end()
                ->end()
            ->end();
            
        foreach ($this->subScopeCollection->get('app') as $scope) {
            $scope->getConfigTreeBuilder( $rootNode );
        }
        
        $rootNode->end();
        
        return $config;
    }
}