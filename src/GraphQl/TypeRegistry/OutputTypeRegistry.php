<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\TypeRegistry;


use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\ObjectType as GraphQlObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use Swift\GraphQl\Exceptions\DuplicateTypeException;
use Swift\GraphQl\TypeRegistryInterface;
use Swift\GraphQl\Types\ObjectType;
use Swift\HttpFoundation\ParameterBag;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Attributes\DI;
use Swift\Kernel\TypeSystem\Enum;

/**
 * Class OutputTypeRegistry
 * @package Swift\GraphQl\TypeRegistry
 */
#[DI(aliases: [TypeRegistryInterface::class . ' $outputTypeRegistry']), Autowire]
class OutputTypeRegistry implements TypeRegistryInterface {

    private array $generators = array();
    private array $definitions = array();
    private array $types = array();
    private ParameterBag $compiled;

    /**
     * OutputTypeRegistry constructor.
     *
     * @param TypeRegistryInterface $interfaceRegistry
     */
    public function __construct(
        private TypeRegistryInterface $interfaceRegistry
    ) {
    }

    /**
     * @inheritDoc
     */
    public function addType( ObjectType $type ): void {
        if (array_key_exists($type->declaringClass, $this->types)) {
            throw new DuplicateTypeException(sprintf('Type %s is already declared', $type->declaringClass));
        }

        $this->types[$type->declaringClass] = $type;
    }

    /**
     * @inheritDoc
     */
    public function addTypes( array $types ): void {
        array_map(fn($type) => $this->addType($type), $types);
    }

    /**
     * @inheritDoc
     */
    public function getTypeByClass( string $name ): ObjectType|Type|null {
        if (array_key_exists($name, \Swift\GraphQl\Types\Type::getStandardTypes())) {
            return \Swift\GraphQl\Types\Type::getStandardTypes()[$name];
        }

        return $this->types[$name] ?? null;
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
        foreach ($this->types as $type) {
            $this->definitions[$type->name] = $this->createObject($type);
        }

        $this->compiled = new ParameterBag($this->definitions);
    }

    public function createObject( $type ) {
        if (!$type->generator && is_array($type->type)) {
            return $this->buildUnion($type);
        }

        if (!$type->generator && array_key_exists($type->type, $this->types)) {
            $type = $this->getTypeByClass($type->type);
        }

        if (is_a(object_or_class: $type, class: \GraphQL\Type\Definition\Type::class, allow_string: false)) {
            return $type;
        }

        $type->type ??= $type->declaringClass;
        $identifier ??= $type->type;

        if (!$type->generator && array_key_exists(key: $identifier, array: $this->definitions)) {
            return $this->definitions[$identifier];
        }

        $fields = $this->resolveFields($type->fields);
        $args = $this->resolveFields($type->args ?? array());

        if ($type->generator) {
            if (!array_key_exists(key: $type->generator, array: $this->generators)) {
                $this->generators[$type->generator] = new $type->generator();
            }
            $generator = $this->generators[$type->generator];
            $object = $generator->generate($type, $this);
        } elseif (array_key_exists($identifier, \Swift\GraphQl\Types\Type::getStandardTypes())) {
            return array(
                'description' => $type->description,
                'defaultValue' => $type->defaultValue,
                'type' => \Swift\GraphQl\Types\Type::getStandardTypes()[$identifier],
            );
        } elseif (is_a(object_or_class: $identifier, class: Enum::class, allow_string: true)) {
            $object = $this->definitions[$identifier] ?? new EnumType(array(
                'name' => (new \ReflectionClass($identifier))->getShortName(),
                'description' => $type->description,
                'values' => $identifier::keys(),
                'declaration' => $type,
            ));
            $this->definitions[$identifier] = $object;
        } else {
            if ($type->defaultValue) {
                var_dump($type);
            }
            $object = new GraphQlObjectType(array(
                'name' => $type->name,
                'description' => $type->description,
                'fields' => $fields,
                'declaration' => $type,
                'interfaces' => $this->interfaceRegistry->fromType($type),
                'args' => $args,
                'defaultValue' => $type->defaultValue,
            ));
            $this->definitions[$identifier] = $object;
        }


        $object = $type->isList ? \Swift\GraphQl\Types\Type::listOf($object) : $object;

        return $object;
    }

    private function buildUnion( $type ): UnionType {
        $name = ucfirst($type->name . 'Union');
        if (array_key_exists($name, $this->definitions)) {
            return $this->definitions[$name];
        }
        $types = array();
        foreach ($type->type as $item) {
            $types[] = $this->createObject($this->getTypeByClass($item));
        }
        $object = new UnionType([
            'name' => $name,
            'description' => $type->description,
            'types' => $types,
        ]);
        $this->definitions[$name] = $object;
        return $object;
    }

    private function resolveFields( array $items ): array {
        $fields = array();
        foreach ($items as $field) {
            if (!$field->type) {
                continue;
            }
            if (is_array($field->type)) {
                return $this->buildUnion($field);
            }
            $field->type = $field->name === 'id' ? 'id' : $field->type;
            $config = array(
                'description' => $field->description,
                'args' => $this->resolveFields($field->args ?? array()),
                'defaultValue' => $field->defaultValue,
            );
            if (array_key_exists($field->type, \Swift\GraphQl\Types\Type::getStandardTypes())) {
                $config['type'] = $field->nullable ?
                    \Swift\GraphQl\Types\Type::getStandardTypes()[ $field->type ] :
                    \Swift\GraphQl\Types\Type::nonNull(\Swift\GraphQl\Types\Type::getStandardTypes()[ $field->type ]);
                $fields[ $field->name ] = $config;
                continue;
            }
            $fieldItem = array_key_exists(key: $field->type, array: $this->definitions) ?
                $this->definitions[$field->type] : $this->createObject($field);
            $config['type'] = $field->isList && (!$fieldItem instanceof ListOfType) ? \Swift\GraphQl\Types\Type::listOf($fieldItem) : $fieldItem;
            $fields[$field->name] = $config;
        }

        return $fields;
    }

}