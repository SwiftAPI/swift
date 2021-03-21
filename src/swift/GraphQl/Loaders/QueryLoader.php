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
use Swift\GraphQl\ResolveHelper;
use Swift\GraphQl\TypeRegistryInterface;
use Swift\GraphQl\Types\ObjectType;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\ContainerAwareTrait;
use Swift\Kernel\ServiceLocatorInterface;

/**
 * Class QueryLoader
 * @package Swift\GraphQl
 */
#[Autowire]
class QueryLoader implements LoaderInterface {

    /**
     * QueryLoader constructor.
     *
     * @param TypeRegistryInterface $inputTypeRegistry
     * @param TypeRegistryInterface $outputTypeRegistry
     * @param TypeRegistryInterface $queryRegistry
     * @param ResolveHelper $helper
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(
        private TypeRegistryInterface $inputTypeRegistry,
        private TypeRegistryInterface $outputTypeRegistry,
        private TypeRegistryInterface $queryRegistry,
        private ResolveHelper $helper,
        private ServiceLocatorInterface $serviceLocator,
    ) {
    }

    public function load( TypeRegistryInterface $typeRegistry ): void {
        $queries = $this->serviceLocator->getServicesByTag('graphql.query');

        foreach ($queries as $query) {
            $classReflection = $this->serviceLocator->getReflectionClass($query);

            if (is_null($classReflection)) {
                continue;
            }

            foreach ($classReflection->getMethods() as $reflectionMethod) {
                /** @var Query $methodConfig */
                $methodConfig = !empty($reflectionMethod->getAttributes(name: Query::class)) ? $reflectionMethod->getAttributes(name: Query::class)[0]->newInstance() : null;

                if (is_null($methodConfig)) {
                    continue;
                }

                $methodParameters = $reflectionMethod->getParameters();
                $arguments = array();

                foreach ($methodParameters as $reflectionParameter) {
                    /** @var Argument $parameterConfig */
                    $parameterConfig = !empty($reflectionParameter->getAttributes(name: Argument::class)) ? $reflectionParameter->getAttributes(name: Argument::class)[0]->newInstance() : null;
                    $argumentType = $this->helper->getArgumentType($parameterConfig?->type, $reflectionParameter?->getType());
                    $argumentName = $parameterConfig->name ?? $reflectionParameter->getName();

                    $arguments[$argumentName] = new ObjectType(
                        name: $argumentName,
                        declaringClass: $reflectionMethod->getDeclaringClass()->getName(),
                        declaringMethod: $methodConfig->name ?? $reflectionMethod->getName(),
                        type: $argumentType,
                        nullable: $reflectionParameter->isOptional(),
                        generator: $parameterConfig->generator ?? null,
                        generatorArguments: $parameterConfig->generatorArguments ?? array(),
                        description: $parameterConfig->description ?? null,
                    );
                }

                $queryName = $methodConfig->name ?? $reflectionMethod->getName();
                $queryType = $this->helper->getReturnType($methodConfig?->type, $reflectionMethod?->getReturnType());
                $objectType = new ObjectType(
                    name: ucfirst($queryName),
                    declaringClass: $reflectionMethod->getDeclaringClass()->getName(),
                    resolve: $reflectionMethod->getName(),
                    args: $arguments,
                    type: $queryType,
                    isList: $methodConfig->isList ?? false,
                    generator: $methodConfig->generator ?? null,
                    generatorArguments: $methodConfig->generatorArguments ?? array(),
                    description: $methodConfig->description ?? null,
                );

                $this->queryRegistry->addType($objectType);
            }
        }
    }

}