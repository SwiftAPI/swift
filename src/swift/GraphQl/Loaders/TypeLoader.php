<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Loaders;

use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\Type;
use Swift\GraphQl\LoaderInterface;
use Swift\GraphQl\TypeRegistryInterface;
use Swift\GraphQl\Types\ObjectType;
use Swift\Kernel\ContainerAwareTrait;
use Swift\Model\Attributes\DBField;

class TypeLoader implements LoaderInterface {

    use ContainerAwareTrait;

    public function load( TypeRegistryInterface $typeRegistry ): void {
        $types = $this->container->getDefinitionsByTag(tag: 'graphql.type');

        foreach ($types as $type) {
            $fields = array();
            $classReflection = $this->container->getReflectionClass($type);
            $typeConfig = $classReflection?->getAttributes(name: Type::class)[0]?->getArguments();
            $typeName = $typeConfig['name'] ?? $classReflection?->getShortName();

            foreach ($classReflection?->getProperties() as $reflectionProperty) {
                $propertyConfig = $reflectionProperty->getAttributes(Field::class);

                // Ignore properties without field annotation unless they have a DBField annotation.
                // This won't be used, but will allow the field to added to the schema
                if (empty($propertyConfig) && empty($reflectionProperty->getAttributes(name: DBField::class))) {
                    continue;
                }

                $propertyConfig = !empty($propertyConfig) ? $propertyConfig[0]->getArguments() : $propertyConfig;

                $fieldName = $propertyConfig['name'] ?? $reflectionProperty->getName();
                $fieldType = $propertyConfig['type'] ?? $reflectionProperty->getType()->getName();
                $nullable = $propertyConfig['nullable'] ?? $reflectionProperty->hasDefaultValue();

                $fields[$fieldName] = new ObjectType(
                    name: $fieldName,
                    declaringClass: $reflectionProperty->getDeclaringClass()->getName(),
                    type: $fieldType,
                    nullable: $nullable,
                    generator: $propertyConfig['generator'] ?? null,
                    generatorArguments: $propertyConfig['generatorArguments'] ?? array(),
                );
            }

            foreach ($classReflection?->getMethods() as $reflectionMethod) {
                $methodConfig = $reflectionMethod?->getAttributes(name: Field::class);
                $methodParameters = $reflectionMethod->getParameters();

                if (empty($methodConfig)) {
                    continue;
                }

                $methodConfig = $methodConfig[0]->getArguments();

                $fieldName = $methodConfig['name'] ?? $reflectionMethod->getName();
                $fieldType = $methodConfig['type'] ?? $reflectionMethod->getReturnType()?->getName();
                $args      = array();

                foreach ($methodParameters as $reflectionParameter) {
                    $parameterConfig = $reflectionParameter->getAttributes(name: Field::class);
                    $parameterFieldName = $parameterConfig['name'] ?? $reflectionParameter->getName();
                    $parameterFieldType = $parameterConfig['type'] ?? $reflectionParameter->getType()->getName();

                    $args[$parameterFieldName] = new ObjectType(
                        name: $parameterFieldName,
                        declaringClass: $reflectionParameter->getDeclaringClass()?->getName(),
                        type: $parameterFieldType,
                        generator: $propertyConfig['generator'] ?? null,
                        generatorArguments: $propertyConfig['generatorArguments'] ?? array(),
                    );
                }

                $fields[$fieldName] = new ObjectType(
                    name: $fieldName,
                    declaringClass: $reflectionMethod->getDeclaringClass()->getName(),
                    args: $args,
                    type: $fieldType,
                    generator: $propertyConfig['generator'] ?? null,
                    generatorArguments: $propertyConfig['generatorArguments'] ?? array(),
                );
            }

            $typeObject = new ObjectType(
                name: $typeConfig['extends'] ?? $typeName,
                declaringClass: $classReflection?->getName(),
                fields: $fields,
            );

            if (!empty($typeConfig['extends'])) {
                $typeRegistry->addExtension($typeObject);
            } else {
                $typeRegistry->addType($typeObject);
            }
        }
    }

}