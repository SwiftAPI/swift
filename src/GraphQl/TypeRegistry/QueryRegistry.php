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
use GraphQLRelay\Relay;
use Swift\GraphQl\ContextInterface;
use Swift\GraphQl\Exceptions\DuplicateTypeException;
use Swift\GraphQl\Resolvers\FieldResolver;
use Swift\GraphQl\TypeRegistryInterface;
use Swift\GraphQl\Types\NodeTypeInterface;
use Swift\GraphQl\Types\ObjectType;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Attributes\DI;
use Swift\Kernel\ServiceLocator;
use Swift\Kernel\TypeSystem\Enum;

/**
 * Class QueryRegistry
 * @package Swift\GraphQl\TypeRegistry
 */
#[DI(aliases: [TypeRegistryInterface::class . ' $queryRegistry']), Autowire]
class QueryRegistry implements TypeRegistryInterface {

    private array $generators = array();
    private array $definitions = array();
    private array $types = array();

    /**
     * QueryRegistry constructor.
     *
     * @param TypeRegistryInterface $inputTypeRegistry
     * @param TypeRegistryInterface $outputTypeRegistry
     * @param TypeRegistryInterface $interfaceRegistry
     * @param FieldResolver $fieldResolver
     * @param ContextInterface $context
     */
    public function __construct(
        private TypeRegistryInterface $inputTypeRegistry,
        private TypeRegistryInterface $outputTypeRegistry,
        private TypeRegistryInterface $interfaceRegistry,
        private FieldResolver $fieldResolver,
        private ContextInterface $context,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function addType( ObjectType $type ): void {
        if (array_key_exists($type->name, $this->types)) {
            throw new DuplicateTypeException(sprintf('Query %s is already declared', $type->name));
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
            'name' => 'Query',
            'description' => 'The root entry point into the Graph',
            'fields' => $fields,
        ));
    }

    /**
     * @inheritDoc
     */
    public function compile(): void {
        foreach ($this->types as $query) {
            /** @var GraphQlObjectType $queryType */
            $queryType = $this->outputTypeRegistry->getCompiled()->get($query->type);

            if (is_null($queryType) ) {
                $queryType = $this->interfaceRegistry->getCompiled()->get($query->type);
            }

            if ($query->name === 'Node') {
                $query->name = 'node';
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

        if ($type->name === 'id') {
            $type->type = 'id';
        }
        $type->type ??= $type->declaringClass;
        $identifier ??= $type->type;

        if ($type->generator) {
            if (!array_key_exists(key: $type->generator, array: $this->generators)) {
                $this->generators[$type->generator] = new $type->generator();
            }
            $generator = $this->generators[$type->generator];
            $object = $generator->generate($type, $this);
        } elseif (is_array($type->type)) {
            return $this->inputTypeRegistry->createObject($type);
        } elseif (array_key_exists($identifier, \Swift\GraphQl\Types\Type::getStandardTypes())) {
            $fieldType = \Swift\GraphQl\Types\Type::getStandardTypes()[$identifier];
            return $type->nullable ? $fieldType : \Swift\GraphQl\Types\Type::nonNull($fieldType);
        } elseif (is_a(object_or_class: $identifier, class: Enum::class, allow_string: true)) {
            $object = new EnumType(array(
                'name' => ucfirst($type->name),
                'values' => $identifier::keys(),
                'declaration' => $type,
            ));
        } elseif ($this->inputTypeRegistry->getCompiled()->has($identifier)) {
            $object = $this->inputTypeRegistry->getCompiled()->get($identifier);
        }

        return $type->isList ? \Swift\GraphQl\Types\Type::listOf($object) : $object;
    }

}