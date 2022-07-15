<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\DependencyInjection;

/**
 * Class Container
 * @package Swift\DependencyInjection
 */
class Container extends \Symfony\Component\DependencyInjection\ContainerBuilder implements ContainerInterface {
    
    private array $resourcePaths;
    
    /**
     * Compiles the container.
     *
     * This method passes the container to compiler
     * passes whose job is to manipulate and optimize
     * the container.
     *
     * The main compiler passes roughly do four things:
     *
     *  * The extension configurations are merged;
     *  * Parameter values are resolved;
     *  * The parameter bag is frozen;
     *  * Extension loading is disabled.
     *
     * @param bool $resolveEnvPlaceholders Whether %env()% parameters should be resolved using the current
     *                                     env vars or be replaced by uniquely identifiable placeholders.
     *                                     Set to "true" when you want to use the current ContainerBuilder
     *                                     directly, keep to "false" when the container is dumped instead.
     */
    public function compile( bool $resolveEnvPlaceholders = false ): void {
        // Support deprecated usage of container global. Usage is highly discouraged. Use injection and compiler passes instead.
        global $container;
        $container = $this;
        
        // Register event dispatcher for dependency injection (that's why it's set to public)
        $this->register( \Swift\Events\EventDispatcher::class, \Swift\Events\EventDispatcher::class )->setPublic( true );
        
        $this->addCompilerPass( new \Swift\DependencyInjection\CompilerPass\DependencyInjectionCompilerPass() );
        $this->addCompilerPass( new \Swift\DependencyInjection\CompilerPass\ExtensionsCompilerPass() );
        
        parent::compile();
        
        
        // Post compile
        ( new \Swift\DependencyInjection\CompilerPass\ExtensionsPostCompilerPass() )->process( $this );
    }
    
    /**
     * Method to get classes by tag
     *
     * @param string $tag
     *
     * @return array
     */
    public function getServicesByTag( string $tag ): array {
        $definitions = [];
        
        if ( empty( $this->getDefinitions() ) ) {
            return $definitions;
        }
        
        $tag = strtolower( $tag );
        foreach ( $this->getDefinitions() as $key => $definition ) {
            if ( $definition->hasTag( $tag ) ) {
                $definitions[] = $key;
            }
        }
        
        return $definitions;
    }
    
    /**
     * Get all service instances for given tag
     *
     * @param string $tag
     *
     * @return array
     * @throws \Exception
     */
    public function getServiceInstancesByTag( string $tag ): array {
        $definitions = [];
        
        if ( empty( $this->getDefinitions() ) ) {
            return $definitions;
        }
        
        foreach ( $this->getDefinitions() as $key => $definition ) {
            if ( $definition->hasTag( $tag ) ) {
                $definitions[] = $this->get( $key );
            }
        }
        
        return $definitions;
    }
    
    /**
     * @return array
     */
    public function getResourcePaths(): array {
        if ( ! isset( $this->resourcePaths ) ) {
            $resources = [];
            foreach ( $this->getResources() as $resource ) {
                if ( $resource instanceof \Symfony\Component\Config\Resource\GlobResource ) {
                    $resources[] = $resource->getPrefix();
                }
            }
            
            $this->resourcePaths = $resources;
        }
        
        return $this->resourcePaths;
    }
    
    public function getReflectionClass( ?string $class, bool $throw = true ): ?\ReflectionClass {
        return parent::getReflectionClass( $class, $throw );
    }
    
    
}