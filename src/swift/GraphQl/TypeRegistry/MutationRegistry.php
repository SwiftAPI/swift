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
use GraphQL\Type\Definition\ObjectType as GraphQlObjectType;
use GraphQL\Type\Definition\Type;
use Swift\GraphQl\Exceptions\DuplicateTypeException;
use Swift\GraphQl\TypeRegistryInterface;
use Swift\GraphQl\Types\ObjectType;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Attributes\DI;
use Swift\Kernel\TypeSystem\Enum;

/**
 * Class MutationRegistry
 * @package Swift\GraphQl\TypeRegistry
 */
#[DI(aliases: [TypeRegistryInterface::class . ' $mutationRegistry']), Autowire]
class MutationRegistry implements TypeRegistryInterface {

    private array $generators = array();
    private array $definitions = array();
    private array $types = array();

    /**
     * QueryRegistry constructor.
     *
     * @param TypeRegistryInterface $inputTypeRegistry
     * @param TypeRegistryInterface $outputTypeRegistry
     */
    public function __construct(
        private TypeRegistryInterface $inputTypeRegistry,
        private TypeRegistryInterface $outputTypeRegistry,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function addType( ObjectType $type ): void {
        if (array_key_exists($type->name, $this->types)) {
            throw new DuplicateTypeException(sprintf('Mutation %s is already declared', $type->name));
        }

        $this->types[$type->name] = $type;
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
     * @inheritDoc
     */
    public function getRootQuery(): GraphQlObjectType {
        $fields = array();

        foreach ($this->definitions as $name => $query) {
            $fields[$name] = $query;
        }

        return new GraphQlObjectType(array(
            'name' => 'Mutation',
            'fields' => $fields,
            'inter'
        ));
    }

    /**
     * @inheritDoc
     */
    public function compile(): void {
        foreach ($this->types as $query) {
            if (is_array($query->type)) {
                /** @var GraphQlObjectType $queryType */
                $queryType = $this->outputTypeRegistry->createObject($query);
            } else {
                /** @var GraphQlObjectType $queryType */
                $queryType = $this->outputTypeRegistry->createObject($this->outputTypeRegistry->getTypeByClass($query->type));
            }

            $this->definitions[$query->name] = array(
                'type' => $query->isList ? \Swift\GraphQl\Types\Type::listOf($queryType) : $queryType,
                'args' => $this->resolveArguments($query->args),
                'declaration' => $query,
                'description' => $query->description ?? null,
            );

        }
    }

    private function resolveArguments( ?array $arguments ): array {
        if (!$arguments || empty($arguments)) {
            return array();
        }

        $resolved = array();
        foreach ($arguments as $type) {
            $queryType = $this->createObject($type);
            $resolved[$queryType->config['alias'] ?? $type->name] = $queryType;
        }

        return $resolved;
    }

    private function createObject( $type ) {
        if (is_a(object_or_class: $type, class: \GraphQL\Type\Definition\Type::class, allow_string: false)) {
            return $type;
        }

        $type->type ??= $type->declaringClass;
        $identifier ??= $type->type;

        if ( $type->generator ) {
            if (!array_key_exists(key: $type->generator, array: $this->generators)) {
                $this->generators[$type->generator] = new $type->generator();
            }
            $generator = $this->generators[$type->generator];
            return $generator->generate($type, $this);
        }

        if (is_array($type->type)) {
            $object = $this->inputTypeRegistry->createObject($type);
            return $type->nullable ? $object : \Swift\GraphQl\Types\Type::nonNull($object);
        }
        if ( array_key_exists($identifier, \Swift\GraphQl\Types\Type::getStandardTypes()) ) {
            $fieldType = \Swift\GraphQl\Types\Type::getStandardTypes()[$identifier];
            return $type->nullable ? $fieldType : \Swift\GraphQl\Types\Type::nonNull($fieldType);
        }

        if ( is_a(object_or_class: $identifier, class: Enum::class, allow_string: true) ) {
            return new EnumType(array(
                'name' => ucfirst($type->name),
                'values' => $identifier::keys(),
                'declaration' => $type,
            ));
        }

        $object = $this->inputTypeRegistry->createObject($this->inputTypeRegistry->getTypeByClass($identifier));

        return $type->nullable ? $object : \Swift\GraphQl\Types\Type::nonNull($object);
    }

}