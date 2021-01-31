<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Loaders;


use Swift\GraphQl\Attributes\Argument;
use Swift\GraphQl\Attributes\Mutation;
use Swift\GraphQl\LoaderInterface;
use Swift\GraphQl\TypeRegistryInterface;
use Swift\GraphQl\Types\ObjectType;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\ContainerAwareTrait;

/**
 * Class MutationLoader
 * @package Swift\GraphQl\Loaders
 */
#[Autowire]
class MutationLoader implements LoaderInterface {

    use ContainerAwareTrait;


    /**
     * Load types into given type registry
     *
     * @param TypeRegistryInterface $typeRegistry
     */
    public function load( TypeRegistryInterface $typeRegistry ): void {
        $mutations = $this->container->getDefinitionsByTag('graphql.mutation');

        foreach ($mutations as $mutation) {
            $classReflection = $this->container->getReflectionClass($mutation);

            if (is_null($classReflection)) {
                continue;
            }

            foreach ($classReflection->getMethods() as $reflectionMethod) {
                $methodAttributes = $reflectionMethod->getAttributes(name: Mutation::class);
                $methodConfig = !empty($methodAttributes) ? $methodAttributes[0]->getArguments() : null;

                if (is_null($methodConfig)) {
                    continue;
                }

                $methodParameters = $reflectionMethod->getParameters();
                $arguments = array();

                foreach ($methodParameters as $reflectionParameter) {
                    $parameterAttributes = $reflectionParameter->getAttributes(name: Argument::class);
                    $parameterConfig = !empty($parameterAttributes) ? $parameterAttributes[0]->getArguments() : null;
                    $argumentType = $parameterConfig['type'] ?? $reflectionParameter->getType()?->getName();
                    $argumentName = $reflectionParameter->getName();

                    $arguments[$argumentName] = new ObjectType(
                        name: $argumentName,
                        declaringClass: $reflectionMethod->getDeclaringClass()->getName(),
                        declaringMethod: $methodConfig['name'] ?? $reflectionMethod->getName(),
                        type: $argumentType,
                        nullable: $reflectionParameter->isOptional(),
                        generator: $parameterConfig['generator'] ?? null,
                        generatorArguments: $parameterConfig['generatorArguments'] ?? array(),
                    );
                }

                $queryName = $methodConfig['name'] ?? $reflectionMethod->getName();
                $queryType = $methodConfig['type'] ?? $reflectionMethod->getReturnType()?->getName();
                $objectType = new ObjectType(
                    name: $queryName,
                    declaringClass: $reflectionMethod->getDeclaringClass()->getName(),
                    resolve: $reflectionMethod->getName(),
                    args: $arguments,
                    type: $queryType,
                    isList: $methodConfig['isList'] ?? false,
                );

                $typeRegistry->addMutation($objectType);
            }
        }
    }

}