<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\TypeRegistry;


use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\Type;
use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Exceptions\DuplicateTypeException;
use Swift\GraphQl\TypeRegistryInterface;
use Swift\GraphQl\Types\ObjectType;
use Swift\HttpFoundation\ParameterBag;
use Swift\Kernel\Attributes\DI;
use Swift\Kernel\TypeSystem\Enum;
use Swift\GraphQl\Attributes\InterfaceType as InterfaceTypeAttribute;

/**
 * Class InterfaceRegistry
 * @package Swift\GraphQl\TypeRegistry
 */
#[DI( aliases: [ TypeRegistryInterface::class . ' $interfaceRegistry' ] )]
class InterfaceRegistry implements TypeRegistryInterface {

    private array $generators = array();
    private array $definitions = array();
    private array $types = array();
    private ParameterBag $compiled;
    private TypeRegistryInterface $outputTypeRegistry;

    /**
     * @inheritDoc
     */
    public function addTypes( array $types ): void {
        array_map( fn( $type ) => $this->addType( $type ), $types );
    }

    /**
     * @inheritDoc
     */
    public function addType( ObjectType $type ): void {
        if ( array_key_exists( $type->declaringClass, $this->types ) ) {
            throw new DuplicateTypeException( sprintf( 'Type %s is already declared', $type->declaringClass ) );
        }

        $this->types[ $type->declaringClass ] = $type;
    }

    /**
     * @inheritDoc
     */
    public function getTypeByClass( string $name ): ObjectType|Type|null {
        return $this->types[ $name ] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getTypes(): array {
        return $this->types;
    }

    /**
     * @inheritDoc
     */
    public function addExtension( ObjectType $type ): void {
        // TODO: Implement addExtension() method.
    }

    /**
     * @return ParameterBag
     */
    public function getCompiled(): ParameterBag {
        return $this->compiled ?? new ParameterBag();
    }

    /**
     * @inheritDoc
     */
    public function compile(): void {
        foreach ( $this->types as $type ) {
            $this->definitions[ $type->name ] = $this->createObject( $type );
        }

        $this->compiled = new ParameterBag( $this->definitions );
    }

    public function fromType( ObjectType $type ): array {
        $objectTypes = $this->makeObjectTypes($type);

        if (empty($objectTypes)) {
            return array();
        }

        $compiled = array();
        foreach ($objectTypes as $objectType) {
            $compiled[] = $this->createObject($objectType);
        }

        return $compiled;
    }

    private function getFields(\ReflectionClass $classReflection, array $fields): array {
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
                $parameterFieldList = $parameterConfig['isList'] ?? false;

                $args[ $parameterFieldName ] = new ObjectType(
                    name: $parameterFieldName,
                    declaringClass: $reflectionParameter->getDeclaringClass()?->getName(),
                    declaringMethod: $methodConfig->name ?? $reflectionMethod->getName(),
                    type: $parameterFieldType,
                    nullable: $reflectionParameter->isOptional(),
                    isList: $parameterFieldList,
                    generator: $propertyConfig->generator ?? null,
                    generatorArguments: $propertyConfig->generatorArguments ?? array(),
                );
            }

            $fields[ $fieldName ] = new ObjectType(
                name: $fieldName,
                declaringClass: $reflectionMethod->getDeclaringClass()->getName(),
                resolve: $reflectionMethod->getName(),
                args: $args,
                type: $fieldType,
                isList: $methodConfig->isList ?? false,
                generator: $propertyConfig->generator ?? null,
                generatorArguments: $propertyConfig->generatorArguments ?? array(),
            );
        }

        return $fields;
    }

    private function makeObjectTypes(ObjectType $type): array {
        if (!class_exists($type->type)) {
            return array();
        }
        $reflection = new \ReflectionClass($type->type);

        if (empty($reflection->getInterfaces())) {
            return array();
        }

        $interfaces = array();
        foreach ($reflection->getInterfaces() as $interfaceReflection) {
            if (empty($interfaceReflection->getAttributes(InterfaceTypeAttribute::class))) {
                continue;
            }

            if (array_key_exists($interfaceReflection->getName(), $this->types)) {
                $interfaces[] = $this->types[$interfaceReflection->getName()];
                continue;
            }

            /** @var InterfaceTypeAttribute $interfaceAttribute */
            $interfaceAttribute = $interfaceReflection->getAttributes(InterfaceTypeAttribute::class)[0]->newInstance();
            $fields = $this->getFields($interfaceReflection, array());

            $object = new ObjectType(
                name: $interfaceAttribute->name,
                declaringClass: $interfaceReflection->getName(),
                fields: $fields,
            );
            $interfaces[] = $object;
            $this->types[$interfaceReflection->getName()] = $object;
        }

        return $interfaces;
    }

    public function createObject( ObjectType|\GraphQL\Type\Definition\Type $type, string $identifier = null ) {
        if ( is_a( object_or_class: $type, class: \GraphQL\Type\Definition\Type::class, allow_string: false ) ) {
            return $type;
        }

        if (!$type->generator && array_key_exists($type->type, $this->types)) {
            $type = $this->getTypeByClass($type->type);
        }

        $type->type ??= $type->declaringClass;
        $identifier ??= $type->type;

        if ( ! $type->generator && array_key_exists( key: $identifier, array: $this->definitions ) ) {
            return $this->definitions[ $identifier ];
        }

        $fields = $this->resolveFields($type->fields ?? array());

        if ( $type->generator ) {
            if ( ! array_key_exists( key: $type->generator, array: $this->generators ) ) {
                $this->generators[ $type->generator ] = new $type->generator();
            }
            $generator = $this->generators[ $type->generator ];
            $object    = $generator->generate( $type, $this );
        } elseif ( array_key_exists( $identifier, \Swift\GraphQl\Types\Type::getStandardTypes() ) ) {
            $standardType = \Swift\GraphQl\Types\Type::getStandardTypes()[ $identifier ];
            $standardType = $type->nullable ? $standardType : \Swift\GraphQl\Types\Type::nonNull($standardType);
            $standardType = $type->isList ? \Swift\GraphQl\Types\Type::listOf($standardType) : $standardType;
            return $standardType;
        } elseif ( is_a( object_or_class: $identifier, class: Enum::class, allow_string: true ) ) {
            $object = $this->outputTypeRegistry->createObject( $type ) ?? new EnumType( array(
                    'name'        => ucfirst($type->name),
                    'values'      => $identifier::keys(),
                    'declaration' => $type,
                ) );
            $this->definitions[ $identifier ] = $object;
        } else {
            $object = new InterfaceType( array(
                'name'        => ucfirst($type->name),
                'fields'      => $fields,
                'declaration' => $type,
            ) );
            $object = $type->nullable ? $object : \Swift\GraphQl\Types\Type::nonNull($object);
            $this->definitions[ $identifier ] = $object;
        }

        $object = $type->isList ? \Swift\GraphQl\Types\Type::listOf($object) : $object;
        return $object;
    }

    private function resolveFields( array $items ): array {
        $fields = array();
        foreach ( $items as $field ) {
            if ( ! $field->type ) {
                continue;
            }

            $field->type = $field->name === 'id' ? 'id' : $field->type;
            $config = array(
                'description' => $field->description,
                'args' => $this->resolveFields($field->args ?? array()),
                'defaultValue' => $field->defaultValue,
            );
            if ( array_key_exists( $field->type, \Swift\GraphQl\Types\Type::getStandardTypes() ) ) {
                $config['type'] = $field->nullable ?
                    \Swift\GraphQl\Types\Type::getStandardTypes()[ $field->type ] :
                    \Swift\GraphQl\Types\Type::nonNull(\Swift\GraphQl\Types\Type::getStandardTypes()[ $field->type ]);
                $config['type'] = $field->isList ? \Swift\GraphQl\Types\Type::listOf($config['type']) : $config['type'];
                $fields[ $field->name ] = $config;
                continue;
            }
            $fieldItem = array_key_exists(key: $field->type, array: $this->definitions) ?
                $this->definitions[$field->type] : $this->createObject($field);
            $config['type'] = $field->isList && (!$fieldItem instanceof ListOfType) ? \Swift\GraphQl\Types\Type::listOf($fieldItem) : $fieldItem;
            $fields[ $field->name ] = $config;
        }

        return $fields;
    }

}