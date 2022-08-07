<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\DependencyInjection\CompilerPass;

use Swift\DependencyInjection\Helper\AttributeHelper;
use Swift\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use ReflectionClass;
use Swift\Kernel\Helpers\Utils;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class DependencyInjectionCompilerPass implements \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface {
    
    /**
     * @inheritDoc
     */
    public function process( ContainerBuilder $container ): void {
        $this->processInitial( $container );
        $this->processSecondary( $container );
    }
    
    /**
     * Initial processing
     *
     * @param ContainerBuilder $container
     *
     * @throws \ReflectionException
     */
    private function processInitial( ContainerBuilder $container ): void {
        foreach ( $container->getDefinitions() as $definition ) {
            $reflection = $container->getReflectionClass( $definition->getClass() );
            $attributes = $this->getAttributes( $reflection );
            
            if ( ! empty( $attributes ) ) {
                if ( ! empty( $attributes[ 'exclude' ] ) && is_bool( $attributes[ 'exclude' ] ) ) {
                    $container->removeDefinition( $definition->getClass() );
                    // If definition is set to be excluded it makes no sense to perform any other actions
                    continue;
                }
                if ( ! empty( $attributes[ 'name' ] ) ) {
                    $container->setAlias( $attributes[ 'name' ], $definition->getClass() );
                    $definition->addTag( 'alias:' . $attributes[ 'name' ] );
                }
                if ( ! empty( $attributes[ 'shared' ] ) && is_bool( $attributes[ 'shared' ] ) ) {
                    $definition->setShared( $attributes[ 'shared' ] );
                }
                if ( ! empty( $attributes[ 'tags' ] ) && is_array( $attributes[ 'tags' ] ) ) {
                    array_map( callback: static fn( $tag ) => $definition->addTag( $tag ), array: $attributes[ 'tags' ] );
                }
                if ( ! empty( $attributes[ 'aliases' ] ) && is_array( $attributes[ 'aliases' ] ) ) {
                    array_map( static function ( $alias ) use ( $container, $definition ) {
                        $definition->addTag( 'alias:' . $alias );
                        $container->setAlias( $alias, $definition->getClass() );
                    }, $attributes[ 'aliases' ] );
                }
                if ( isset( $attributes[ 'autowire' ] ) && is_bool( $attributes[ 'autowire' ] ) ) {
                    $definition->setAutowired( $attributes[ 'autowire' ] );
                }
            }
            
            // If a class implements interfaces create an alias so it can also be injected through interface aliasing
            // @see https://symfony.com/doc/current/service_container/autowiring.html#dealing-with-multiple-implementations-of-the-same-type
            if ( ! empty( $reflection?->getInterfaces() ) ) {
                foreach ( $reflection?->getInterfaces() as $interface ) {
                    $container->setAlias( $interface->getName() . ' $' . Utils::classFqnToAliasVariable( $definition->getClass() ), $definition->getClass() );
                    $definition->addTag( 'alias:' . $interface->getName() . ' $' . Utils::classFqnToAliasVariable( $definition->getClass() ) );
                    $definition->addTag( 'alias:' . $interface->getName() . ' $' . Utils::classFqnToAliasVariable( $reflection->getShortName() ) );
                }
            }
            
            $this->applyTags( $container, $definition );
        }
    }
    
    /**
     * @param ReflectionClass $reflection
     * @param array           $arguments
     *
     * @return array
     * @throws \ReflectionException
     */
    private function getAttributes( ReflectionClass $reflection, array $arguments = [] ): array {
        $attributes   = ! empty( AttributeHelper::getDiAttributes( $reflection ) ) ? AttributeHelper::getDiAttributes( $reflection )[ 0 ]->getArguments() : [];
        $autowireAttr = ! empty( AttributeHelper::getAutowireAttributes( $reflection ) );
        
        if ( $autowireAttr && ! isset( $attributes[ 'autowire' ] ) ) {
            $attributes[ 'autowire' ] = $autowireAttr;
        }
        
        if ( ! empty( $attributes ) ) {
            if ( ! isset( $arguments[ 'name' ] ) && isset( $attributes[ 'name' ] ) ) {
                $arguments[ 'name' ] = $attributes[ 'name' ];
            }
            if ( ! isset( $arguments[ 'shared' ] ) && isset( $attributes[ 'shared' ] ) && is_bool( $attributes[ 'shared' ] ) ) {
                $arguments[ 'shared' ] = $attributes[ 'shared' ];
            }
            if ( ! isset( $arguments[ 'exclude' ] ) && isset( $attributes[ 'exclude' ] ) && is_bool( $attributes[ 'exclude' ] ) ) {
                $arguments[ 'exclude' ] = $attributes[ 'exclude' ];
            }
            if ( ! isset( $arguments[ 'autowire' ] ) && isset( $attributes[ 'autowire' ] ) && is_bool( $attributes[ 'autowire' ] ) ) {
                $arguments[ 'autowire' ] = $attributes[ 'autowire' ];
            }
            if ( ! empty( $attributes[ 'aliases' ] ) && is_array( $attributes[ 'aliases' ] ) ) {
                if ( ! isset( $arguments[ 'aliases' ] ) ) {
                    $arguments[ 'aliases' ] = [];
                }
                foreach ( $attributes[ 'aliases' ] as $alias ) {
                    if ( str_starts_with( haystack: $alias, needle: '!' ) && in_array( ltrim( string: $alias, characters: '!' ), $arguments[ 'aliases' ], true ) ) {
                        unset( $arguments[ 'aliases' ][ ltrim( string: $alias, characters: '!' ) ] );
                        continue;
                    }
                    if ( ! in_array( needle: $alias, haystack: $arguments[ 'aliases' ], strict: true ) ) {
                        $arguments[ 'aliases' ][] = $alias;
                    }
                }
            }
            if ( ! empty( $attributes[ 'tags' ] ) && is_array( $attributes[ 'tags' ] ) ) {
                if ( ! isset( $arguments[ 'tags' ] ) ) {
                    $arguments[ 'tags' ] = [];
                }
                foreach ( $attributes[ 'tags' ] as $tag ) {
                    if ( str_starts_with( haystack: $tag, needle: '!' ) && in_array( ltrim( string: $tag, characters: '!' ), $arguments[ 'tags' ], true ) ) {
                        unset( $arguments[ 'tags' ][ ltrim( string: $tag, characters: '!' ) ] );
                        continue;
                    }
                    if ( ! in_array( needle: $tag, haystack: $arguments[ 'tags' ], strict: true ) ) {
                        $arguments[ 'tags' ][] = $tag;
                    }
                }
            }
        }
        
        // Inherit settings form parent classes
        if ( $reflection->getParentClass() ) {
            $parentReflection = new ReflectionClass( $reflection->getParentClass()->getName() );
            $arguments        = $this->getAttributes( reflection: $parentReflection, arguments: $arguments );
        }
        
        // Inherit settings from interfaces
        if ( $reflection->getInterfaces() ) {
            foreach ( $reflection->getInterfaces() as $interface ) {
                $parentReflection = new ReflectionClass( $interface->getName() );
                $arguments        = $this->getAttributes( reflection: $parentReflection, arguments: $arguments );
            }
        }
        
        // Inherit settings from traits
        if ( $reflection->getTraits() ) {
            foreach ( $reflection->getTraits() as $trait ) {
                $parentReflection = new ReflectionClass( $trait->getName() );
                $arguments        = $this->getAttributes( reflection: $parentReflection, arguments: $arguments );
            }
        }
        
        return $arguments;
    }
    
    /**
     * @param Container  $container
     * @param Definition $definition
     *
     * @throws \ReflectionException
     */
    public function applyTags( Container $container, Definition $definition ): void {
        $reflection = $container->getReflectionClass( $definition->getClass() );
        
        if ( $reflection->implementsInterface( EventSubscriberInterface::class ) ) {
            $definition->addTag( 'kernel.event_subscriber' );
        }
    }
    
    /**
     * Process task depending on settings from first initial processing
     *
     * @param ContainerBuilder $container
     *
     * @throws \ReflectionException
     */
    public function processSecondary( ContainerBuilder $container ): void {
        foreach ( $container->getDefinitions() as $definition ) {
            $reflection = $container->getReflectionClass( $definition->getClass() );
            
            foreach ( $reflection->getMethods() as $reflectionMethod ) {
                if ( ! empty( AttributeHelper::getAutowireAttributes( $reflectionMethod ) ) ) {
                    $this->setAutowiredMethodCall( $container, $definition, $reflectionMethod );
                }
            }
            
        }
    }
    
    /**
     * Autowire given method
     *
     * @param ContainerBuilder  $container
     * @param Definition        $definition
     * @param \ReflectionMethod $reflectionMethod
     *
     * @throws \Exception
     */
    public function setAutowiredMethodCall( ContainerBuilder $container, Definition $definition, \ReflectionMethod $reflectionMethod ): void {
        $parameters = [];
        foreach ( $reflectionMethod->getParameters() as $reflectionParameter ) {
            $reflectionAttributes = ! empty( AttributeHelper::getAutowireAttributes( $reflectionParameter ) ) ? AttributeHelper::getAutowireAttributes( $reflectionParameter )[ 0 ]->getArguments() : [];
            $paramType            = $reflectionAttributes[ 'serviceId' ] ?? $reflectionParameter->getType()?->getName();
            
            // Check if the type can be resolved as a service
            if ( $container->has( $paramType ) ) {
                $parameters[] = new Reference( $paramType );
                $definition->addMethodCall( $reflectionMethod->getName(), $parameters );
                continue;
            }
            
            // Check if the type can be resolved as a service
            if ( $container->has( $paramType . ' $' . $reflectionParameter->getName() ) ) {
                $parameters[] = new Reference( $paramType . ' $' . $reflectionParameter->getName() );
                $definition->addMethodCall( $reflectionMethod->getName(), $parameters );
                continue;
            }
            
            // Check if it has an attribute which can be resolved
            if ( $reflectionAttributes ) {
                $tag = $reflectionAttributes[ 'tag' ] ?? '';
                $definition->addMethodCall( $reflectionMethod->getName(), [ new TaggedIteratorArgument( $tag ) ] );
                continue;
            }
            
            // If we arrive here something is wrong
            throw new \InvalidArgumentException( sprintf( 'Argument %s in setter %s in class %s could not be resolved', $reflectionParameter->getName(), $reflectionMethod->getName(), $reflectionMethod->getDeclaringClass()->getName() ) );
        }
    }
}