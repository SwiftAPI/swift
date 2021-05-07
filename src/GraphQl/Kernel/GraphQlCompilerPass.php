<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Kernel;

use Swift\GraphQl\Attributes\InputType;
use Swift\GraphQl\Attributes\Mutation;
use Swift\GraphQl\Attributes\Query;
use Swift\GraphQl\Attributes\Type;
use Swift\GraphQl\GraphQlDiTags;
use Swift\Kernel\Attributes\DI;
use Swift\Kernel\Container\CompilerPass\CompilerPassInterface;
use Swift\Kernel\KernelDiTags;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class GraphQlCompilerPass
 * @package Swift\GraphQl\Kernel
 */
#[DI(tags: [KernelDiTags::COMPILER_PASS])]
class GraphQlCompilerPass implements CompilerPassInterface {

    /**
     * @inheritDoc
     */
    public function process( ContainerBuilder $container ): void {
        foreach ($container->getDefinitions() as $definition) {
            $classReflection = $container->getReflectionClass($definition->getClass());

            if (!empty($classReflection?->getAttributes(name: Type::class))) {
                $definition->addTag(name: GraphQlDiTags::GRAPHQL_TYPE);
            }

            if (!empty($classReflection?->getAttributes(name: InputType::class))) {
                $definition->addTag( name: GraphQlDiTags::GRAPHQL_INPUT_TYPE );
            }

            foreach ($classReflection?->getMethods() as $reflectionMethod) {
                if (!empty($reflectionMethod->getAttributes(name: Query::class))) {
                    $definition->addTag(name: GraphQlDiTags::GRAPHQl_QUERY);
                }
                if (!empty($reflectionMethod->getAttributes(name: Mutation::class))) {
                    $definition->addTag(name: GraphQlDiTags::GRAPHQL_MUTATION);
                }
            }
        }
    }
}