<?php declare(strict_types=1);

namespace Swift\GraphQl\Kernel;

use Swift\GraphQl\Attributes\Mutation;
use Swift\GraphQl\Attributes\Query;
use Swift\GraphQl\Attributes\Type;
use Swift\Kernel\Attributes\DI;
use Swift\Kernel\DiTags;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class GraphQlCompilerPass
 * @package Swift\GraphQl\Kernel
 */
#[DI(tags: [DiTags::COMPILER_PASS])]
class GraphQlCompilerPass implements CompilerPassInterface {

    /**
     * @inheritDoc
     */
    public function process( ContainerBuilder $container ): void {
        foreach ($container->getDefinitions() as $definition) {
            $classReflection = $container->getReflectionClass($definition->getClass());

            if (!empty($classReflection?->getAttributes(name: Type::class))) {
                $definition->addTag(name: 'graphql.type');
            }

            foreach ($classReflection?->getMethods() as $reflectionMethod) {
                if (!empty($reflectionMethod->getAttributes(name: Query::class))) {
                    $definition->addTag(name: 'graphql.query');
                }
                if (!empty($reflectionMethod->getAttributes(name: Mutation::class))) {
                    $definition->addTag(name: 'graphql.mutation');
                }
            }
        }
    }
}