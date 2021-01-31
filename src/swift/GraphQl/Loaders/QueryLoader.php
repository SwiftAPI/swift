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
use Swift\GraphQl\Attributes\Query;
use Swift\GraphQl\LoaderInterface;
use Swift\GraphQl\TypeRegistryInterface;
use Swift\GraphQl\Types\ObjectType;
use Swift\Kernel\ContainerAwareTrait;

/**
 * Class QueryLoader
 * @package Swift\GraphQl
 */
class QueryLoader implements LoaderInterface {

    use ContainerAwareTrait;

    public function load( TypeRegistryInterface $typeRegistry ): void {
        $queries = $this->container->getDefinitionsByTag('graphql.query');

        foreach ($queries as $query) {
            $classReflection = $this->container->getReflectionClass($query);

            if (is_null($classReflection)) {
                continue;
            }

            foreach ($classReflection->getMethods() as $reflectionMethod) {
                $methodConfig = !empty($reflectionMethod->getAttributes(name: Query::class)) ? $reflectionMethod->getAttributes(name: Query::class)[0]->getArguments() : null;

                if (is_null($methodConfig)) {
                    continue;
                }

                $methodParameters = $reflectionMethod->getParameters();
                $arguments = array();

                foreach ($methodParameters as $reflectionParameter) {
                    $parameterConfig = !empty($reflectionParameter->getAttributes(name: Argument::class)) ? $reflectionParameter->getAttributes(name: Argument::class)[0]->getArguments() : null;
                    $argumentType = $parameterConfig['type'] ?? $reflectionParameter->getType()?->getName();
                    $argumentName = $parameterConfig['name'] ?? $reflectionParameter->getName();

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
                    generator: $propertyConfig['generator'] ?? null,
                    generatorArguments: $propertyConfig['generatorArguments'] ?? array(),
                );

                $typeRegistry->addQuery($objectType);
            }
        }
    }

}