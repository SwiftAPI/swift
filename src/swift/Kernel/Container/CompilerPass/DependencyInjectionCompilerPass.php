<?php declare( strict_types=1 );


namespace Swift\Kernel\Container\CompilerPass;

use Honeywell\Controller\ConditionController;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Container\Container;
use Swift\Kernel\Container\TaggedServices;
use Swift\Kernel\ServiceLocator;
use Swift\Kernel\ServiceLocatorInterface;
use Swift\Users\Controller\User;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use ReflectionClass;
use Swift\Kernel\Attributes\DI;
use Swift\Kernel\Helpers\Utils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

class DependencyInjectionCompilerPass implements CompilerPassInterface {

    /**
     * @inheritDoc
     */
    public function process( ContainerBuilder $container ) {
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
                if ( ! empty( $attributes['exclude'] ) && is_bool( $attributes['exclude'] ) ) {
                    $container->removeDefinition( $definition->getClass() );
                    // If definition is set to be excluded it makes no sense to perform any other actions
                    continue;
                }
                if ( ! empty( $attributes['name'] ) ) {
                    $container->setAlias( $attributes['name'], $definition->getClass() );
                }
                if ( ! empty( $attributes['shared'] ) && is_bool( $attributes['shared'] ) ) {
                    $definition->setShared( $attributes['shared'] );
                }
                if ( ! empty( $attributes['tags'] ) && is_array( $attributes['tags'] ) ) {
                    array_map( callback: fn( $tag ) => $definition->addTag( $tag ), array: $attributes['tags'] );
                }
                if ( ! empty( $attributes['aliases'] ) && is_array( $attributes['aliases'] ) ) {
                    array_map( callback: fn( $alias ) => $container->setAlias( $alias, $definition->getClass() ), array: $attributes['aliases'] );
                }
                if ( isset( $attributes['autowire'] ) && is_bool( $attributes['autowire'] ) ) {
                    $definition->setAutowired( $attributes['autowire'] );
                }
            }

            // If a class implements interfaces create an alias so it can also be injected through interface aliasing
            // @see https://symfony.com/doc/current/service_container/autowiring.html#dealing-with-multiple-implementations-of-the-same-type
            if ( ! empty( $reflection?->getInterfaces() ) ) {
                foreach ( $reflection?->getInterfaces() as $interface ) {
                    $container->setAlias( $interface->getName() . ' $' . Utils::classFqnToAliasVariable( $definition->getClass() ), $definition->getClass() );
                }
            }

            $this->applyTags( $container, $definition );
        }
    }

    /**
     * @param ReflectionClass $reflection
     * @param array $arguments
     *
     * @return array
     * @throws \ReflectionException
     */
    private function getAttributes( ReflectionClass $reflection, array $arguments = array() ): array {
        $attributes   = !empty($reflection?->getAttributes( DI::class )) ? $reflection?->getAttributes( DI::class )[0]->getArguments() : array();
        $autowireAttr = ! empty( $reflection?->getAttributes( Autowire::class ) );

        if ( $autowireAttr && !isset($attributes['autowire']) ) {
            $attributes['autowire'] = $autowireAttr;
        }

        if ( ! empty( $attributes ) ) {
            if ( ! isset( $arguments['name'] ) && isset( $attributes['name'] ) ) {
                $arguments['name'] = $attributes['name'];
            }
            if ( ! isset( $arguments['shared'] ) && isset( $attributes['shared'] ) && is_bool( $attributes['shared'] ) ) {
                $arguments['shared'] = $attributes['shared'];
            }
            if ( ! isset( $arguments['exclude'] ) && isset( $attributes['exclude'] ) && is_bool( $attributes['exclude'] ) ) {
                $arguments['exclude'] = $attributes['exclude'];
            }
            if ( ! isset( $arguments['autowire'] ) && isset( $attributes['autowire'] ) && is_bool( $attributes['autowire'] ) ) {
                $arguments['autowire'] = $attributes['autowire'];
            }
            if ( ! empty( $attributes['aliases'] ) && is_array( $attributes['aliases'] ) ) {
                if ( ! isset( $arguments['aliases'] ) ) {
                    $arguments['aliases'] = array();
                }
                foreach ( $attributes['aliases'] as $alias ) {
                    if ( str_starts_with( haystack: $alias, needle: '!' ) && in_array( ltrim( string: $alias, characters: '!' ), $arguments['aliases'], true ) ) {
                        unset( $arguments['aliases'][ ltrim( string: $alias, characters: '!' ) ] );
                        continue;
                    }
                    if ( ! in_array( needle: $alias, haystack: $arguments['aliases'], strict: true ) ) {
                        $arguments['aliases'][] = $alias;
                    }
                }
            }
            if ( ! empty( $attributes['tags'] ) && is_array( $attributes['tags'] ) ) {
                if ( ! isset( $arguments['tags'] ) ) {
                    $arguments['tags'] = array();
                }
                foreach ( $attributes['tags'] as $tag ) {
                    if ( str_starts_with( haystack: $tag, needle: '!' ) && in_array( ltrim( string: $tag, characters: '!' ), $arguments['tags'], true ) ) {
                        unset( $arguments['tags'][ ltrim( string: $tag, characters: '!' ) ] );
                        continue;
                    }
                    if ( ! in_array( needle: $tag, haystack: $arguments['tags'], strict: true ) ) {
                        $arguments['tags'][] = $tag;
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
     * @param Container $container
     * @param Definition $definition
     *
     * @throws \ReflectionException
     */
    public function applyTags( Container $container, Definition $definition ): void {
        $reflection = $container->getReflectionClass( $definition->getClass() );

        if ( $reflection?->isSubclassOf( Command::class ) ) {
            $definition->addTag( 'kernel.command' );
        }

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
                if ( ! empty( $reflectionMethod->getAttributes( Autowire::class ) ) ) {
                    $this->setAutowiredMethodCall( $container, $definition, $reflectionMethod );
                }
            }

        }
    }

    /**
     * Autowire given method
     *
     * @param ContainerBuilder $container
     * @param Definition $definition
     * @param \ReflectionMethod $reflectionMethod
     *
     * @throws \Exception
     */
    public function setAutowiredMethodCall( ContainerBuilder $container, Definition $definition, \ReflectionMethod $reflectionMethod ): void {
        $parameters = array();
        foreach ( $reflectionMethod->getParameters() as $reflectionParameter ) {
            $reflectionAttributes = ! empty( $reflectionParameter->getAttributes( Autowire::class ) ) ? $reflectionParameter->getAttributes( Autowire::class )[0]->getArguments() : array();
            $paramType            = $reflectionAttributes['serviceId'] ?? $reflectionParameter->getType()?->getName();

            // Check if the type can be resolved as a service
            if ( ($paramType !== TaggedServices::class) && $container->has( $paramType ) ) {
                $parameters[] = new Reference($paramType);
                $definition->addMethodCall( $reflectionMethod->getName(), $parameters );
                continue;
            }

            // Check if it has an attribute which can be resolved
            if ( $reflectionAttributes ) {
                $tag          = $reflectionAttributes['tag'] ?? '';
                $definition->addMethodCall($reflectionMethod->getName(), [new TaggedIteratorArgument($tag)]);
                continue;
            }

            // If we arrive here something is wrong
            throw new \InvalidArgumentException( sprintf( 'Argument %s in setter %s could not be resolved', $reflectionParameter->getName(), $reflectionMethod->getName() ) );
        }
    }
}