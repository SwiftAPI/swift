<?php declare( strict_types=1 );

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
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\ServiceLocatorInterface;
use Swift\Model\Attributes\DBField;

/**
 * Class OutputTypeLoader
 * @package Swift\GraphQl\Loaders
 */
#[Autowire]
class OutputTypeLoader implements LoaderInterface {

    /**
     * InputTypeLoader constructor.
     *
     * @param TypeRegistryInterface $outputTypeRegistry
     * @param TypeRegistryInterface $interfaceRegistry
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(
        private TypeRegistryInterface $outputTypeRegistry,
        private TypeRegistryInterface $interfaceRegistry,
        private ServiceLocatorInterface $serviceLocator,
    ) {
    }

    public function load( TypeRegistryInterface $typeRegistry ): void {
        $types = $this->serviceLocator->getServicesByTag( 'graphql.type' );

        foreach ( $types as $type ) {
            if ($typeRegistry->getTypeByClass($type)) {
                continue;
            }
            $this->loadType( $type, $typeRegistry );
        }
    }

    private function loadType( $type, TypeRegistryInterface $typeRegistry ) {
        $fields          = array();
        $classReflection = $this->serviceLocator->getReflectionClass( $type );
        /** @var Type $typeConfig */
        $typeConfig      = $classReflection?->getAttributes( name: Type::class )[0]?->newInstance();
        $typeName        = $typeConfig->name ?? $classReflection?->getShortName();

        $fields = $this->getFields($classReflection, $fields);

        foreach ( $classReflection?->getProperties() as $reflectionProperty ) {
            $propertyAttributes = $reflectionProperty->getAttributes( Field::class );
            /** @var Field $propertyConfig */
            $propertyConfig = !empty($propertyAttributes) ? $propertyAttributes[0]->newInstance() : null;

            // Ignore properties without field annotation unless they have a DBField annotation.
            // This won't be used, but will allow the field to added to the schema
            if ( !$propertyConfig && empty( $reflectionProperty->getAttributes( name: DBField::class ) ) ) {
                continue;
            }

            $fieldName = $propertyConfig->name ?? $reflectionProperty->getName();
            $fieldType = $propertyConfig->type ?? $reflectionProperty->getType()->getName();
            $nullable  = $propertyConfig->nullable ?? $reflectionProperty->hasDefaultValue();

            $fields[ $fieldName ] = new ObjectType(
                name: $fieldName,
                declaringClass: $reflectionProperty->getDeclaringClass()->getName(),
                type: $fieldType,
                nullable: $nullable,
                generator: $propertyConfig->generator ?? null,
                generatorArguments: $propertyConfig->generatorArguments ?? array(),
                description: $propertyConfig->description ?? null,
            );
        }

        foreach ( $classReflection?->getMethods() as $reflectionMethod ) {
            $methodConfig     = $reflectionMethod?->getAttributes( name: Field::class );
            $methodParameters = $reflectionMethod->getParameters();

            if ( empty( $methodConfig ) ) {
                continue;
            }

            /** @var Field $methodConfig */
            $methodConfig = $methodConfig[0]->newInstance();

            $fieldName = $methodConfig->name ?? $reflectionMethod->getName();
            $fieldType = $methodConfig->type ?? $reflectionMethod->getReturnType()?->getName();
            $args      = array();

            foreach ( $methodParameters as $reflectionParameter ) {
                $parameterConfig    = $reflectionParameter->getAttributes( name: Field::class );
                $parameterFieldName = $parameterConfig['name'] ?? $reflectionParameter->getName();
                $parameterFieldType = $parameterConfig['type'] ?? $reflectionParameter->getType()->getName();

                $args[ $parameterFieldName ] = new ObjectType(
                    name: $parameterFieldName,
                    declaringClass: $reflectionParameter->getDeclaringClass()?->getName(),
                    declaringMethod: $methodConfig->name ?? $reflectionMethod->getName(),
                    type: $parameterFieldType,
                    nullable: $reflectionParameter->isOptional(),
                    generator: $parameterConfig->generator ?? null,
                    generatorArguments: $parameterConfig->generatorArguments ?? array(),
                    description: $parameterConfig->description ?? null,
                );
            }

            $fields[ $fieldName ] = new ObjectType(
                name: $fieldName,
                declaringClass: $reflectionMethod->getDeclaringClass()->getName(),
                declaringMethod: $reflectionMethod->getName(),
                resolve: $reflectionMethod->getName(),
                args: $args,
                type: $fieldType,
                isList: $methodConfig->isList ?? false,
                generator: $methodConfig->generator ?? null,
                generatorArguments: $methodConfig->generatorArguments ?? array(),
                description: $methodConfig->description,
            );
        }

        $typeObject = new ObjectType(
            name: $typeConfig->extends ?? $typeName,
            declaringClass: $classReflection?->getName(),
            fields: $fields,
            description: $typeConfig->description,
        );

        if ( ! empty( $typeConfig->extends ) ) {
            $typeRegistry->addExtension( $typeObject );
        } else {
            $this->outputTypeRegistry->addType( $typeObject );
        }

        return $typeObject;
    }

    private function getFields(\ReflectionClass $classReflection, array $fields): array {
        foreach ( $classReflection?->getProperties() as $reflectionProperty ) {
            $propertyAttributes = $reflectionProperty->getAttributes( Field::class );
            /** @var Field $propertyConfig */
            $propertyConfig = !empty($propertyAttributes) ? $propertyAttributes[0]->newInstance() : null;

            // Ignore properties without field annotation unless they have a DBField annotation.
            // This won't be used, but will allow the field to added to the schema
            if ( !$propertyConfig && empty( $reflectionProperty->getAttributes( name: DBField::class ) ) ) {
                continue;
            }

            $fieldName = $propertyConfig->name ?? $reflectionProperty->getName();
            $fieldType = $propertyConfig->type ?? $reflectionProperty->getType()->getName();
            $nullable  = $propertyConfig->nullable ?? $reflectionProperty->hasDefaultValue();

            $fields[ $fieldName ] = new ObjectType(
                name: $fieldName,
                declaringClass: $reflectionProperty->getDeclaringClass()->getName(),
                type: $fieldType,
                nullable: $nullable,
                generator: $propertyConfig->generator ?? null,
                generatorArguments: $propertyConfig->generatorArguments ?? array(),
            );
        }

        return $fields;
    }

}