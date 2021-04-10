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
use Swift\GraphQl\Attributes\InterfaceType;
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

            if (!empty($classReflection?->getAttributes(name: InputType::class))) {
                $definition->addTag( name: 'graphql.input_type' );
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