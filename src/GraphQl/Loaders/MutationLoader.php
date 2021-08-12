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
use Swift\GraphQl\GraphQlDiTags;
use Swift\GraphQl\LoaderInterface;
use Swift\GraphQl\ResolveHelper;
use Swift\GraphQl\TypeRegistryInterface;
use Swift\GraphQl\Types\ObjectType;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\ContainerAwareTrait;
use Swift\Kernel\ServiceLocatorInterface;

/**
 * Class MutationLoader
 * @package Swift\GraphQl\Loaders
 */
#[Autowire]
class MutationLoader implements LoaderInterface {

    /**
     * MutationLoader constructor.
     *
     * @param TypeRegistryInterface $inputTypeRegistry
     * @param TypeRegistryInterface $mutationRegistry
     * @param ServiceLocatorInterface $serviceLocator
     * @param ResolveHelper $helper
     */
    public function __construct(
        private TypeRegistryInterface $inputTypeRegistry,
        private TypeRegistryInterface $mutationRegistry,
        private ServiceLocatorInterface $serviceLocator,
        private ResolveHelper $helper,
    ) {
    }

    /**
     * Load types into given type registry
     *
     * @param TypeRegistryInterface $typeRegistry
     */
    public function load( TypeRegistryInterface $typeRegistry ): void {
        $mutations = $this->serviceLocator->getServicesByTag(GraphQlDiTags::GRAPHQL_MUTATION);

        foreach ($mutations as $mutation) {
            $classReflection = $this->serviceLocator->getReflectionClass($mutation);

            if (is_null($classReflection)) {
                continue;
            }

            foreach ($classReflection->getMethods() as $reflectionMethod) {
                $methodAttributes = $reflectionMethod->getAttributes(name: Mutation::class);
                /** @var Mutation $methodConfig */
                $methodConfig = !empty($methodAttributes) ? $methodAttributes[0]->newInstance() : null;

                if (is_null($methodConfig)) {
                    continue;
                }

                $methodParameters = $reflectionMethod->getParameters();
                $arguments = array();

                foreach ($methodParameters as $reflectionParameter) {
                    $parameterAttributes = $reflectionParameter->getAttributes(name: Argument::class);
                    /** @var Argument $parameterConfig */
                    $parameterConfig = !empty($parameterAttributes) ? $parameterAttributes[0]->newInstance() : null;
                    $argumentType = $this->helper->getArgumentType($parameterConfig?->type, $reflectionParameter?->getType());
                    $argumentName = $reflectionParameter->getName();

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
                //$queryType = $methodConfig->type ?? $reflectionMethod->getReturnType()?->getName();
                $queryType = $this->helper->getReturnType($methodConfig?->type, $reflectionMethod?->getReturnType());
                $objectType = new ObjectType(
                    name: ucfirst($queryName),
                    declaringClass: $reflectionMethod->getDeclaringClass()->getName(),
                    resolve: $reflectionMethod->getName(),
                    args: $arguments,
                    type: $queryType,
                    isList: $methodConfig->isList ?? false,
                    description: $methodConfig->description ?? null,
                    authTypes: $methodConfig?->getAuthTypes(),
                    isGranted: $methodConfig?->getIsGranted(),
                );

                $this->mutationRegistry->addType($objectType);
            }
        }
    }

}