<?php declare(strict_types=1);


namespace Swift\Kernel\ContainerService\CompilerPass;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use ReflectionClass;
use Swift\Kernel\Attributes\DI;
use Swift\Kernel\ContainerService\ContainerService;
use Swift\Kernel\Helpers\Utils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;

class DependencyInjectionCompilerPass implements CompilerPassInterface {

    /**
     * @inheritDoc
     */
    public function process( ContainerBuilder $container ) {
        foreach ($container->getDefinitions() as $definition) {
            $reflection = $container->getReflectionClass($definition->getClass());

            $attributes = $this->getAttributes($reflection);

            if (!empty($attributes)) {
                if (!empty($attributes['exclude']) && is_bool($attributes['exclude'])) {
                    $container->removeDefinition($definition->getClass());
                    // If definition is set to be excluded it makes no sense to perform any other actions
                    continue;
                }
                if (!empty($attributes['name'])) {
                    $container->setAlias($attributes['name'], $definition->getClass());
                }
                if (!empty($attributes['shared']) && is_bool($attributes['shared'])) {
                    $definition->setShared($attributes['shared']);
                }
                if (!empty($attributes['tags']) && is_array($attributes['tags'])) {
                    array_map(callback: fn ($tag) => $definition->addTag($tag), array: $attributes['tags']);
                }
                if (isset($attributes['autowire']) && is_bool($attributes['autowire'])) {
                    $definition->setAutowired($attributes['autowire']);
                }
            }

            // If a class implements interfaces create an alias so it can also be injected through interface aliasing
            // @see https://symfony.com/doc/current/service_container/autowiring.html#dealing-with-multiple-implementations-of-the-same-type
            if (!empty($reflection?->getInterfaces())) {
                foreach ($reflection?->getInterfaces() as $interface) {
                    $container->setAlias($interface->getName() . ' $' . Utils::classFqnToAliasVariable($definition->getClass()), $definition->getClass());
                }
            }

            $this->applyTags($container, $definition);
        }
    }

    public function applyTags( ContainerService $container, Definition $definition ): void {
        $reflection = $container->getReflectionClass($definition->getClass());

        if ($reflection?->isSubclassOf(Command::class)) {
            $definition->addTag('kernel.command');
        }

        if ($reflection->implementsInterface(EventSubscriberInterface::class)) {
            $definition->addTag('kernel.event_subscriber');
        }
    }

    private function getAttributes( ReflectionClass $reflection, array $arguments = array()): array {
        $attributes = $reflection?->getAttributes(DI::class);

        if (!empty($attributes)) {
            $attributes = $attributes[0]->getArguments();

            if (!isset($arguments['name']) && isset($attributes['name'])) {
                $arguments['name'] = $attributes['name'];
            }
            if (!isset($arguments['shared']) && isset($attributes['shared']) && is_bool($attributes['shared'])) {
                $arguments['shared'] = $attributes['shared'];
            }
            if (!isset($arguments['exclude']) && isset($attributes['exclude']) && is_bool($attributes['exclude'])) {
                $arguments['exclude'] = $attributes['exclude'];
            }
            if (!isset($arguments['autowire']) && isset($attributes['autowire']) && is_bool($attributes['autowire'])) {
                $arguments['autowire'] = $attributes['autowire'];
            }
            if (!empty($attributes['tags']) && is_array($attributes['tags'])) {
                if (!isset($arguments['tags'])) {
                    $arguments['tags'] = array();
                }
                foreach ($attributes['tags'] as $tag) {
                    if ( str_starts_with(haystack: $tag, needle: '!') && in_array( ltrim( string: $tag, characters: '!' ), $arguments['tags'], true ) ) {
                        unset($arguments['tags'][ltrim( string: $tag, characters: '!' )]);
                        continue;
                    }
                    if (!in_array(needle: $tag, haystack: $arguments['tags'], strict: true)) {
                        $arguments['tags'][] = $tag;
                    }
                }
            }
        }

        if ($reflection->getParentClass()) {
            $parentReflection = new ReflectionClass($reflection->getParentClass()->getName());
            $arguments = $this->getAttributes(reflection: $parentReflection, arguments: $arguments);
        }

        return $arguments;
    }
}