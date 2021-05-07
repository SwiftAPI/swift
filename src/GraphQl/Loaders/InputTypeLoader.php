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
use Swift\GraphQl\Attributes\InputType;
use Swift\GraphQl\Attributes\Type;
use Swift\GraphQl\GraphQlDiTags;
use Swift\GraphQl\LoaderInterface;
use Swift\GraphQl\ResolveHelper;
use Swift\GraphQl\TypeRegistryInterface;
use Swift\GraphQl\Types\ObjectType;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\ContainerAwareTrait;
use Swift\Kernel\ServiceLocatorInterface;
use Swift\Model\Attributes\DBField;
use Swift\Security\User\Type\TokenType;

/**
 * Class InputTypeLoader
 * @package Swift\GraphQl\Loaders
 */
#[Autowire]
class InputTypeLoader implements LoaderInterface {

    /**
     * InputTypeLoader constructor.
     *
     * @param TypeRegistryInterface $inputTypeRegistry
     * @param ServiceLocatorInterface $serviceLocator
     * @param ResolveHelper $helper
     */
    public function __construct(
        private TypeRegistryInterface $inputTypeRegistry,
        private ServiceLocatorInterface $serviceLocator,
        private ResolveHelper $helper,
    ) {
    }

    public function load( TypeRegistryInterface $typeRegistry ): void {
        $types = $this->serviceLocator->getServicesByTag( GraphQlDiTags::GRAPHQL_INPUT_TYPE );

        foreach ( $types as $type ) {
            if ($this->inputTypeRegistry->getTypeByClass($type)) {
                continue;
            }
            $this->loadType( $type, $typeRegistry );
        }
    }

    private function loadType( $type, TypeRegistryInterface $typeRegistry ) {
        $fields          = array();
        $classReflection = $this->serviceLocator->getReflectionClass( $type );
        /** @var InputType $typeConfig */
        $typeConfig      = $classReflection?->getAttributes( name: InputType::class )[0]?->newInstance();
        $typeName        = $typeConfig->name ?? $classReflection?->getShortName();

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
            $fieldType = $this->helper->getArgumentType($propertyConfig?->type, $reflectionProperty?->getType());
            $nullable  = $propertyConfig->nullable ?? $reflectionProperty->hasDefaultValue();

            $fields[ $fieldName ] = new ObjectType(
                name: $fieldName,
                declaringClass: $reflectionProperty->getDeclaringClass()->getName(),
                type: $fieldType,
                defaultValue: $propertyConfig->defaultValue ?? $this->helper->getDefaultValue($reflectionProperty),
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
                $parameterConfig    = !empty($reflectionParameter->getAttributes( name: Field::class )) ?
                    $reflectionParameter->getAttributes( name: Field::class )[0]->newInstance() : new Field();
                $parameterFieldName = $parameterConfig->name ?? $reflectionParameter->getName();
                $parameterFieldType = $parameterConfig->type ?? $reflectionParameter->getType()->getName();

                $args[ $parameterFieldName ] = new ObjectType(
                    name: $parameterFieldName,
                    declaringClass: $reflectionParameter->getDeclaringClass()?->getName(),
                    type: $parameterFieldType,
                    defaultValue: $parameterConfig->defaultValue ?? $this->helper->getDefaultValue($reflectionParameter),
                    generator: $methodConfig->generator ?? null,
                    generatorArguments: $methodConfig->generatorArguments ?? array(),
                    description: $parameterConfig->description ?? null,
                );
            }

            $fields[ $fieldName ] = new ObjectType(
                name: $fieldName,
                declaringClass: $reflectionMethod->getDeclaringClass()->getName(),
                args: $args,
                type: $fieldType,
                defaultValue: $methodConfig->defaultValue,
                generator: $methodConfig->generator ?? null,
                generatorArguments: $methodConfig->generatorArguments ?? array(),
                description: $methodConfig->description ?? null,
            );
        }

        $typeObject = new ObjectType(
            name: ucfirst($typeConfig->extends ?? $typeName),
            declaringClass: $classReflection?->getName(),
            fields: $fields,
            description: $typeConfig->description,
        );

        if ( ! empty( $typeConfig->extends ) ) {
            $typeRegistry->addExtension( $typeObject );
        } else {
            $this->inputTypeRegistry->addType( $typeObject );
        }

        return $typeObject;
    }


}